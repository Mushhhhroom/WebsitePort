<?php
/**
 * Security, Sanitization & Authentication Utilities
 * Portfolio Project - Jairus John Valdez
 * Implements:
 * - Deterministic HMAC-SHA256 Stateless Auth Tokens (Fixes Serverless Lambda Login Loops on Vercel)
 * - Double-Submit Cookie CSRF Protection (Resilient across multi-worker serverless invocations)
 * - XSS Output Escaping & Input Sanitization
 * - Strict Security Response Headers
 */

// 1. Send Security Response Headers
function set_security_headers(): void {
    if (!headers_sent()) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }
}

// 2. Initialize Secure Session
function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                    (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_set_cookie_params([
            'lifetime' => 86400 * 7,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $is_https,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }
}

// 3. Cryptographic Secret Key for Stateless Tokens
function get_auth_secret(): string {
    $secret = getenv('AUTH_SECRET');
    if (!empty($secret)) {
        return $secret;
    }
    $db_pass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') ?: '');
    return hash('sha256', $db_pass . '_portfolio_secure_salt_jairus_2026_!@#');
}

// 4. Generate Cryptographically Signed Stateless Auth Token
function generate_auth_token(array $user): string {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'uid'   => (int)$user['id'],
        'user'  => (string)$user['username'],
        'email' => (string)($user['email'] ?? ''),
        'name'  => (string)($user['full_name'] ?? $user['name'] ?? 'Admin'),
        'iat'   => time(),
        'exp'   => time() + (86400 * 7) // 7 days persistent session
    ]);

    $b64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
    $b64Payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    $sig = hash_hmac('sha256', "{$b64Header}.{$b64Payload}", get_auth_secret(), true);
    $b64Sig = rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

    return "{$b64Header}.{$b64Payload}.{$b64Sig}";
}

// 5. Verify Cryptographically Signed Stateless Auth Token
function verify_auth_token(?string $token): ?array {
    if (empty($token) || !is_string($token)) {
        return null;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    list($b64Header, $b64Payload, $b64Sig) = $parts;
    $expectedSig = rtrim(strtr(base64_encode(hash_hmac('sha256', "{$b64Header}.{$b64Payload}", get_auth_secret(), true)), '+/', '-_'), '=');

    if (!hash_equals($expectedSig, $b64Sig)) {
        return null;
    }

    $payloadJson = base64_decode(strtr($b64Payload, '-_', '+/'));
    $payload = json_decode($payloadJson, true);
    if (!is_array($payload) || empty($payload['uid'])) {
        return null;
    }

    if (empty($payload['exp']) || (int)$payload['exp'] < time()) {
        return null;
    }

    return $payload;
}

// 6. Set and Clear Stateless Auth Cookies
function set_auth_cookie(array $user): void {
    $token = generate_auth_token($user);
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $options = [
        'expires'  => time() + (86400 * 7),
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    if (!headers_sent()) {
        setcookie('portfolio_auth_token', $token, $options);
    }
    $_COOKIE['portfolio_auth_token'] = $token;
}

function clear_auth_cookie(): void {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    if (!headers_sent()) {
        setcookie('portfolio_auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $is_https,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    unset($_COOKIE['portfolio_auth_token']);
}

// 7. XSS Protection: HTML Escaping Helper
function e(?string $value): string {
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// 8. Input Sanitization
function sanitize_text(?string $data): string {
    if ($data === null) {
        return '';
    }
    return trim(strip_tags($data));
}

// 9. CSRF Token Generator (Double-Submit Cookie backed for Serverless resiliency)
function generate_csrf_token(): string {
    start_secure_session();

    // Re-use valid cookie token if already set to keep token deterministic across serverless invocations
    if (!empty($_COOKIE['portfolio_csrf']) && preg_match('/^[a-f0-9]{64}$/i', $_COOKIE['portfolio_csrf'])) {
        $_SESSION['csrf_token'] = $_COOKIE['portfolio_csrf'];
        return $_COOKIE['portfolio_csrf'];
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    if (!headers_sent()) {
        setcookie('portfolio_csrf', $_SESSION['csrf_token'], [
            'expires'  => time() + (86400 * 7),
            'path'     => '/',
            'domain'   => '',
            'secure'   => $is_https,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    $_COOKIE['portfolio_csrf'] = $_SESSION['csrf_token'];

    return $_SESSION['csrf_token'];
}

// 10. CSRF Form Field HTML Helper
function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

// 11. CSRF Verification Helper (Validates against Session OR Cookie for Serverless)
function verify_csrf_token(?string $token): bool {
    start_secure_session();
    if (empty($token) || !is_string($token)) {
        return false;
    }

    // Direct match against session variable
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }

    // Double-submit cookie verification (protects against serverless memory evaporation)
    if (!empty($_COOKIE['portfolio_csrf']) && hash_equals($_COOKIE['portfolio_csrf'], $token)) {
        $_SESSION['csrf_token'] = $_COOKIE['portfolio_csrf'];
        return true;
    }

    return false;
}

// 12. Auth State Helpers
function is_logged_in(): bool {
    start_secure_session();

    // Check active memory session first
    if (!empty($_SESSION['user_id']) && !empty($_SESSION['username'])) {
        return true;
    }

    // Stateless fallback: Hydrate from cryptographically signed cookie (crucial for Vercel/AWS Lambda)
    if (!empty($_COOKIE['portfolio_auth_token'])) {
        $payload = verify_auth_token($_COOKIE['portfolio_auth_token']);
        if ($payload && !empty($payload['uid'])) {
            $_SESSION['user_id']    = (int)$payload['uid'];
            $_SESSION['username']   = (string)$payload['user'];
            $_SESSION['user_email'] = (string)($payload['email'] ?? '');
            $_SESSION['user_name']  = (string)($payload['name'] ?? 'Admin');
            $_SESSION['logged_at']  = (int)($payload['iat'] ?? time());
            return true;
        } else {
            clear_auth_cookie();
        }
    }

    return false;
}

function require_login(): void {
    if (!is_logged_in()) {
        if (!headers_sent()) {
            header("Location: login.php?error=auth_required");
            exit;
        }
        echo "<script>window.location.href = 'login.php?error=auth_required';</script>";
        exit;
    }
}

function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => (int)$_SESSION['user_id'],
        'username' => (string)$_SESSION['username'],
        'email'    => (string)($_SESSION['user_email'] ?? ''),
        'name'     => (string)($_SESSION['user_name'] ?? 'Admin')
    ];
}

// Initialize headers and session automatically
set_security_headers();
start_secure_session();
// Self-hydrate session if cookie token exists
is_logged_in();
