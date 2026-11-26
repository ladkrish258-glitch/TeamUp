<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
require_once dirname(__DIR__) . '/Components/Connection/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../../LandingPanel/Pages/Login/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../Post/post.php");
  exit;
}

$userId = $_SESSION['user_id'];
$content = trim($_POST['post_content'] ?? '');

// ---------- Handle photo upload ----------
$photoUrl = null;
if (!empty($_FILES['post_photo']['name']) && $_FILES['post_photo']['error'] === UPLOAD_ERR_OK) {
  $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
  $ext = strtolower(pathinfo($_FILES['post_photo']['name'], PATHINFO_EXTENSION));

  if (in_array($ext, $allowed)) {
    $uploadDir = dirname(__DIR__, 4) . '/images/User/Post/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
      chmod($uploadDir, 0777);
    }
    $fileName = 'post_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['post_photo']['tmp_name'], $destPath)) {
      chmod($destPath, 0666);
      $photoUrl = '../../../../images/User/Post/' . $fileName;
    } else {
      error_log("Failed to move uploaded file from " . $_FILES['post_photo']['tmp_name'] . " to " . $destPath);
    }
  }
}

if ($content === '' && !$photoUrl) {
  die("Please add some content or a photo before posting.");
}

// ---------- Insert into user_posts ----------
$stmt = $pdo->prepare("INSERT INTO user_posts (user_id, content, photo_url, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$userId, $content, $photoUrl]);
$postId = $pdo->lastInsertId();

// ---------- Insert into matches (linked via post_id) ----------
$sportId    = $_POST['sport_id'] ?? null;
$groundId   = $_POST['ground_id'] ?? null;
$matchDate  = $_POST['match_date'] ?? null;
$startTime  = $_POST['start_time'] ?? null;
$endTime    = $_POST['end_time'] ?? null;
$maxPlayers = $_POST['max_players'] ?? null;
$skillLevel = $_POST['skill_level_match'] ?? ($_POST['skill_level'] ?? null);
$visibility = $_POST['visibility'] ?? 'Public';
$entryFee   = $_POST['entry_fee'] !== '' ? $_POST['entry_fee'] : null;

$sportName = '';
if ($sportId) {
  $sportStmt = $pdo->prepare("SELECT sport_name FROM sports WHERE sport_id = ?");
  $sportStmt->execute([$sportId]);
  $sportName = $sportStmt->fetchColumn() ?: 'Sport';
}
$title = $sportName . ' Match - ' . ($matchDate ? date('d M Y', strtotime($matchDate)) : date('d M Y'));

$stmt = $pdo->prepare("INSERT INTO matches
    (post_id, sports_id, created_by, ground_id, title, description, match_date, start_time, end_time,
     max_players, current_players, skill_level, visibility, entry_fee, match_status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 'Open', NOW(), NOW())");

$stmt->execute([
    $postId,
    $sportId,
    $userId,
    $groundId,
    $title,
    $content,
    $matchDate,
    $startTime,
    $endTime,
    $maxPlayers,
    $skillLevel,
    $visibility,
    $entryFee,
]);

// ---------- Redirect back to feed ----------
header("Location: ../Home/home.php?posted=1");
exit;