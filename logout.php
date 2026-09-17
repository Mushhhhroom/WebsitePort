<?php
/**
 * Logout Handler (logout.php)
 * Portfolio Project - Jairus John Valdez
 * Destroys session securely and invalidates session cookie
 */

require_once __DIR__ . '/includes/security.php';

start_secure_session();

// Unset all session variables
$_SESSION = [];

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

header("Location: index.php?msg=logged_out");
exit;
