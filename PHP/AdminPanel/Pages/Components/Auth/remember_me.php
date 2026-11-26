<?php
// Remember-me (persistent login) helper for the admin panel.
// Include this file AFTER a mysqli connection ($conn) exists, or pass your own.
// Requires the admin_remember_tokens table — see admin_remember_tokens.sql

if (!defined('REMEMBER_COOKIE_NAME')) {
    define('REMEMBER_COOKIE_NAME', 'admin_remember');
}
if (!defined('REMEMBER_COOKIE_DAYS')) {
    define('REMEMBER_COOKIE_DAYS', 30);
}

function remember_cookie_params()
{
    return [
        'expires'  => time() + (REMEMBER_COOKIE_DAYS * 86400),
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

/**
 * Issue a fresh remember-me token for this admin and set the cookie.
 */
function issue_remember_token(mysqli $conn, int $admin_id): void
{
    $selector         = bin2hex(random_bytes(12)); // 24 hex chars
    $validator        = bin2hex(random_bytes(32)); // 64 hex chars
    $hashed_validator = hash('sha256', $validator);
    $expires_at       = date('Y-m-d H:i:s', time() + (REMEMBER_COOKIE_DAYS * 86400));

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO admin_remember_tokens (admin_id, selector, hashed_validator, expires_at) VALUES (?, ?, ?, ?)"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isss", $admin_id, $selector, $hashed_validator, $expires_at);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    setcookie(REMEMBER_COOKIE_NAME, $selector . ':' . $validator, remember_cookie_params());
    $_COOKIE[REMEMBER_COOKIE_NAME] = $selector . ':' . $validator;
}

/**
 * Delete the remember-me token (DB row + cookie) tied to this browser.
 */
function clear_remember_token(mysqli $conn): void
{
    if (!empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
        $parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
        if (count($parts) === 2) {
            $selector = $parts[0];
            $stmt = mysqli_prepare($conn, "DELETE FROM admin_remember_tokens WHERE selector = ?");
            mysqli_stmt_bind_param($stmt, "s", $selector);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $params = remember_cookie_params();
    $params['expires'] = time() - 3600;
    setcookie(REMEMBER_COOKIE_NAME, '', $params);
    unset($_COOKIE[REMEMBER_COOKIE_NAME]);
}

/**
 * Try to restore a login purely from the remember-me cookie.
 * On success, populates $_SESSION and rotates the token. Returns true/false.
 */
function attempt_remember_login(mysqli $conn): bool
{
    if (empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
        return false;
    }

    $parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
    if (count($parts) !== 2) {
        clear_remember_token($conn);
        return false;
    }
    $selector  = $parts[0];
    $validator = $parts[1];

    $stmt = mysqli_prepare($conn, "SELECT * FROM admin_remember_tokens WHERE selector = ? AND expires_at > NOW()");
    mysqli_stmt_bind_param($stmt, "s", $selector);
    mysqli_stmt_execute($stmt);
    $token_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$token_row || !hash_equals($token_row['hashed_validator'], hash('sha256', $validator))) {
        // Missing, expired, or tampered-with token — clean up and force a fresh login
        clear_remember_token($conn);
        return false;
    }

    $stmt_admin = mysqli_prepare($conn, "SELECT * FROM admins WHERE admin_id = ?");
    mysqli_stmt_bind_param($stmt_admin, "i", $token_row['admin_id']);
    mysqli_stmt_execute($stmt_admin);
    $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_admin));
    mysqli_stmt_close($stmt_admin);

    if (!$admin || (isset($admin['status']) && $admin['status'] === 'Inactive')) {
        clear_remember_token($conn);
        return false;
    }

    // Rotate: invalidate the used token, issue a new one
    $del = mysqli_prepare($conn, "DELETE FROM admin_remember_tokens WHERE selector = ?");
    mysqli_stmt_bind_param($del, "s", $selector);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);

    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        session_regenerate_id(true);
    }
    $_SESSION['username'] = $admin['email'];
    $_SESSION['admin']    = $admin['username'];
    $_SESSION['admin_id'] = $admin['admin_id'];

    issue_remember_token($conn, (int)$admin['admin_id']);

    return true;
}