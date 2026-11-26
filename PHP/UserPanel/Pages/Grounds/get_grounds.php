<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/Components/Connection/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['city'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$city = $_GET['city'];
$sportId = $_GET['sport_id'] ?? null;

if ($sportId) {
    $stmt = $pdo->prepare("SELECT ground_id, ground_name
                          FROM grounds
                          WHERE city = ? AND sport_id = ? AND status = 'Available'
                          ORDER BY ground_name");
    $stmt->execute([$city, $sportId]);
} else {
    $stmt = $pdo->prepare("SELECT ground_id, ground_name
                          FROM grounds
                          WHERE city = ? AND status = 'Available'
                          ORDER BY ground_name");
    $stmt->execute([$city]);
}

$grounds = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($grounds);