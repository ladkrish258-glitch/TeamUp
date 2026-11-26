<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please log in first.']);
    exit;
}

require_once dirname(__DIR__) . '/Components/Connection/db.php';

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'join';
$matchId = isset($_POST['match_id']) ? (int)$_POST['match_id'] : (isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0);

if ($matchId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid match ID.']);
    exit;
}

if ($action === 'join') {
    // Check if match exists
    $stmt = $pdo->prepare("SELECT match_id, title, max_players, current_players, match_status, created_by FROM matches WHERE match_id = ?");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$match) {
        echo json_encode(['success' => false, 'error' => 'Match not found.']);
        exit;
    }

    if ($match['match_status'] !== 'Open') {
        echo json_encode(['success' => false, 'error' => 'This match is not open for joining.']);
        exit;
    }

    // Check if user is already joined
    $checkStmt = $pdo->prepare("SELECT * FROM match_participants WHERE match_id = ? AND user_id = ?");
    $checkStmt->execute([$matchId, $userId]);
    $alreadyJoined = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$alreadyJoined) {
        // Insert participant
        $insert = $pdo->prepare("INSERT INTO match_participants (match_id, user_id, join_status) VALUES (?, ?, 'Approved')");
        $insert->execute([$matchId, $userId]);
    }

    // Recalculate actual current_players from match_participants count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM match_participants WHERE match_id = ?");
    $countStmt->execute([$matchId]);
    $actualCount = (int)$countStmt->fetchColumn();

    $updCurrent = $pdo->prepare("UPDATE matches SET current_players = ? WHERE match_id = ?");
    $updCurrent->execute([$actualCount, $matchId]);

    // Fetch updated match numbers
    $refreshStmt = $pdo->prepare("SELECT current_players, max_players FROM matches WHERE match_id = ?");
    $refreshStmt->execute([$matchId]);
    $updated = $refreshStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'         => true,
        'already_joined'  => (bool)$alreadyJoined,
        'current_players' => (int)$updated['current_players'],
        'max_players'     => (int)$updated['max_players'],
        'slots_remaining' => max(0, (int)$updated['max_players'] - (int)$updated['current_players']),
    ]);
    exit;
}

if ($action === 'leave') {
    // Check if match exists
    $stmt = $pdo->prepare("SELECT match_id, title, max_players, current_players, match_status, created_by FROM matches WHERE match_id = ?");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$match) {
        echo json_encode(['success' => false, 'error' => 'Match not found.']);
        exit;
    }

    if ((int)$match['created_by'] === $userId) {
        echo json_encode(['success' => false, 'error' => 'As the host/organizer, you cannot leave your own match.']);
        exit;
    }

    // Delete participant
    $del = $pdo->prepare("DELETE FROM match_participants WHERE match_id = ? AND user_id = ?");
    $del->execute([$matchId, $userId]);

    // Recalculate actual current_players from match_participants count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM match_participants WHERE match_id = ?");
    $countStmt->execute([$matchId]);
    $actualCount = (int)$countStmt->fetchColumn();

    $updCurrent = $pdo->prepare("UPDATE matches SET current_players = ? WHERE match_id = ?");
    $updCurrent->execute([$actualCount, $matchId]);

    // Fetch updated match numbers
    $refreshStmt = $pdo->prepare("SELECT current_players, max_players FROM matches WHERE match_id = ?");
    $refreshStmt->execute([$matchId]);
    $updated = $refreshStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'         => true,
        'action'          => 'leave',
        'current_players' => (int)$updated['current_players'],
        'max_players'     => (int)$updated['max_players'],
        'slots_remaining' => max(0, (int)$updated['max_players'] - (int)$updated['current_players']),
    ]);
    exit;
}

if ($action === 'get_players') {
    // 1. Get Match & Creator Info
    $stmtMatch = $pdo->prepare("
        SELECT m.match_id, m.title, m.current_players, m.max_players, m.created_by,
               s.sport_name,
               u.full_name as creator_name, u.city as creator_city, u.profile_photo as creator_photo
        FROM matches m
        LEFT JOIN sports s ON m.sports_id = s.sport_id
        LEFT JOIN users u ON m.created_by = u.user_id
        WHERE m.match_id = ?
    ");
    $stmtMatch->execute([$matchId]);
    $matchData = $stmtMatch->fetch(PDO::FETCH_ASSOC);

    if (!$matchData) {
        echo json_encode(['success' => false, 'error' => 'Match not found.']);
        exit;
    }

    // 2. Get participants from match_participants
    $stmtPart = $pdo->prepare("
        SELECT mp.participant_id, mp.joined_at, mp.join_status,
               u.user_id, u.full_name, u.city, u.profile_photo
        FROM match_participants mp
        JOIN users u ON mp.user_id = u.user_id
        WHERE mp.match_id = ?
        ORDER BY mp.joined_at ASC
    ");
    $stmtPart->execute([$matchId]);
    $participants = $stmtPart->fetchAll(PDO::FETCH_ASSOC);

    $players = [];
    $creatorIncluded = false;

    // Check if creator is already in participants list
    foreach ($participants as $p) {
        $isHost = ((int)$p['user_id'] === (int)$matchData['created_by']);
        if ($isHost) {
            $creatorIncluded = true;
        }
        $players[] = [
            'user_id'       => (int)$p['user_id'],
            'full_name'     => $p['full_name'],
            'city'          => $p['city'] ?? '',
            'profile_photo' => $p['profile_photo'] ?? '',
            'is_host'       => $isHost,
            'joined_at'     => !empty($p['joined_at']) ? date('M d, Y', strtotime($p['joined_at'])) : 'Joined',
        ];
    }

    // If host was not in match_participants, prepend the creator as host
    if (!$creatorIncluded && !empty($matchData['created_by'])) {
        array_unshift($players, [
            'user_id'       => (int)$matchData['created_by'],
            'full_name'     => $matchData['creator_name'] ?? 'Organizer',
            'city'          => $matchData['creator_city'] ?? '',
            'profile_photo' => $matchData['creator_photo'] ?? '',
            'is_host'       => true,
            'joined_at'     => 'Organizer',
        ]);
    }

    echo json_encode([
        'success'         => true,
        'match_id'        => $matchId,
        'title'           => $matchData['title'] ?? ($matchData['sport_name'] ?? 'Match'),
        'current_players' => count($players),
        'max_players'     => (int)$matchData['max_players'],
        'players'         => $players,
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action.']);
