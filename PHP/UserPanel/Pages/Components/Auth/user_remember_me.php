<?php
// Persistent login (Remember-me) helper for UserPanel.
// Requires PDO connection ($pdo).

if (!defined('USER_REMEMBER_COOKIE_NAME')) {
    define('USER_REMEMBER_COOKIE_NAME', 'user_remember');
}
if (!defined('USER_REMEMBER_COOKIE_DAYS')) {
    define('USER_REMEMBER_COOKIE_DAYS', 30);
}

function user_remember_cookie_params(): array
{
    return [
        'expires'  => time() + (USER_REMEMBER_COOKIE_DAYS * 86400),
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

/**
 * Configure and start session with 30-day persistent cookie lifetime if not active.
 */
function ensure_user_session_started(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_set_cookie_params([
                'lifetime' => USER_REMEMBER_COOKIE_DAYS * 86400,
                'path'     => '/',
                'domain'   => '',
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        @session_start();
    }
}

/**
 * Issue a fresh remember-me token for a user and set the cookie.
 */
function issue_user_remember_token(PDO $pdo, int $userId): void
{
    $selector         = bin2hex(random_bytes(12)); // 24 hex characters
    $validator        = bin2hex(random_bytes(32)); // 64 hex characters
    $hashedValidator  = hash('sha256', $validator);
    $expiresAt        = date('Y-m-d H:i:s', time() + (USER_REMEMBER_COOKIE_DAYS * 86400));

    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_remember_tokens (user_id, selector, hashed_validator, expires_at)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $selector, $hashedValidator, $expiresAt]);

        $cookieValue = $selector . ':' . $validator;
        setcookie(USER_REMEMBER_COOKIE_NAME, $cookieValue, user_remember_cookie_params());
        $_COOKIE[USER_REMEMBER_COOKIE_NAME] = $cookieValue;
    } catch (Exception $e) {
        error_log("Failed to issue user remember token: " . $e->getMessage());
    }
}

/**
 * Delete the remember-me token from DB and clear cookie.
 */
function clear_user_remember_token(PDO $pdo): void
{
    if (!empty($_COOKIE[USER_REMEMBER_COOKIE_NAME])) {
        $parts = explode(':', $_COOKIE[USER_REMEMBER_COOKIE_NAME], 2);
        if (count($parts) === 2) {
            $selector = $parts[0];
            try {
                $stmt = $pdo->prepare("DELETE FROM user_remember_tokens WHERE selector = ?");
                $stmt->execute([$selector]);
            } catch (Exception $e) {
                // ignore
            }
        }
    }

    $params = user_remember_cookie_params();
    $params['expires'] = time() - 3600;
    setcookie(USER_REMEMBER_COOKIE_NAME, '', $params);
    unset($_COOKIE[USER_REMEMBER_COOKIE_NAME]);
}

/**
 * Attempt to restore a user session from the remember-me cookie.
 * Returns true if successfully authenticated, false otherwise.
 */
function attempt_user_remember_login(PDO $pdo): bool
{
    if (empty($_COOKIE[USER_REMEMBER_COOKIE_NAME])) {
        return false;
    }

    $parts = explode(':', $_COOKIE[USER_REMEMBER_COOKIE_NAME], 2);
    if (count($parts) !== 2) {
        clear_user_remember_token($pdo);
        return false;
    }

    $selector  = $parts[0];
    $validator = $parts[1];

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM user_remember_tokens 
            WHERE selector = ? AND expires_at > NOW() 
            LIMIT 1
        ");
        $stmt->execute([$selector]);
        $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokenRow || !hash_equals($tokenRow['hashed_validator'], hash('sha256', $validator))) {
            clear_user_remember_token($pdo);
            return false;
        }

        // Fetch user from DB
        $userStmt = $pdo->prepare("SELECT user_id, username, email, status FROM users WHERE user_id = ? LIMIT 1");
        $userStmt->execute([(int)$tokenRow['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || (isset($user['status']) && in_array(strtolower($user['status']), ['inactive', 'unactive', 'blocked']))) {
            clear_user_remember_token($pdo);
            return false;
        }

        // Rotate token (delete used one and issue fresh)
        $del = $pdo->prepare("DELETE FROM user_remember_tokens WHERE selector = ?");
        $del->execute([$selector]);

        ensure_user_session_started();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['username'] = $user['username'] ?? $user['email'];

        issue_user_remember_token($pdo, (int)$user['user_id']);

        return true;
    } catch (Exception $e) {
        error_log("Error in attempt_user_remember_login: " . $e->getMessage());
        return false;
    }
}
