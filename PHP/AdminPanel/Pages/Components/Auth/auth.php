<?php
// Prevent browser caching on all admin pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400 * 30,
        'cookie_httponly' => true,
        'use_only_cookies' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
    ]);
}

if (empty($_SESSION['admin_id']) || empty($_SESSION['admin'])) {
    // No active session — before giving up, see if a "remember me" cookie
    // can restore the login (e.g. browser was closed and reopened later).
    require_once __DIR__ . '/remember_me.php';

    $auth_conn = @mysqli_connect("localhost", "root", "", "teamup");
    $restored = $auth_conn ? attempt_remember_login($auth_conn) : false;
    if ($auth_conn) {
        mysqli_close($auth_conn);
    }

    if (!$restored) {
        // Truly logged out (or session expired/cleared by logout) — kick back to login
        session_unset();
        session_destroy();
        header("Location: /Team-Up/PHP/AdminPanel/Pages/Components/Login/login.php");
        exit();
    }
}

?>