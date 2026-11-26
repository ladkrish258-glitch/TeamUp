<?php
require_once dirname(__DIR__) . '/Components/Connection/db.php';
require_once dirname(__DIR__) . '/Components/Auth/user_remember_me.php';

ensure_user_session_started();
clear_user_remember_token($pdo);

// Clear session variables and session cookie
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header("Location: ../../../LandingPanel/Pages/Login/login.php");
exit;
?>
