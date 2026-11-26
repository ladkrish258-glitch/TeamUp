<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once dirname(__DIR__) . '/Components/Connection/db.php';

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit;
}

// Fetch user profile
$stmt = $pdo->prepare("SELECT user_id, full_name, username, area, city, state, country, profile_photo, created_at FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Format avatar photo
$avatar = null;
if (!empty($user['profile_photo'])) {
    $raw = trim($user['profile_photo']);
    if (strpos($raw, 'http') === 0 || strpos($raw, '/') === 0 || strpos($raw, '../') === 0) {
        $avatar = strpos($raw, '/Team-Up/') === 0 ? '../../../../' . substr($raw, 9) : $raw;
    } else {
        $avatar = '../../../../images/User/Profile/' . ltrim($raw, '/');
    }
}
$user['avatar_url'] = $avatar;
$user['initial'] = strtoupper(substr($user['full_name'] ?? 'U', 0, 1));
$user['member_since'] = !empty($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : 'Recent';

// Fetch sports
$stmtSports = $pdo->prepare("
    SELECT s.sport_name, us.skill_level, us.experience_years, us.preferred_position
    FROM user_sports us
    JOIN sports s ON us.sport_id = s.sport_id
    WHERE us.user_id = ?
    ORDER BY s.sport_name ASC
");
$stmtSports->execute([$userId]);
$sports = $stmtSports->fetchAll(PDO::FETCH_ASSOC);

// Fetch match counts
$stmtMatches = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE created_by = ?");
$stmtMatches->execute([$userId]);
$matchesCount = (int)$stmtMatches->fetchColumn();

echo json_encode([
    'success' => true,
    'user' => $user,
    'sports' => $sports,
    'matches_count' => $matchesCount
]);
