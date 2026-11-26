<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/Auth/remember_me.php';

$conn = @mysqli_connect("localhost", "root", "", "teamup");
if ($conn) {
    clear_remember_token($conn);
    mysqli_close($conn);
}

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: /Team-Up/PHP/AdminPanel/Pages/Components/Login/login.php");
exit();