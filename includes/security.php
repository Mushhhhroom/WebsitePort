<?php
/**
 * Security & Sanitization Utilities
 * Portfolio Project - Jairus John Valdez
 * Implements CSRF protection, XSS escaping, Secure Sessions, and Auth Guards
 */

// 1. Send Security Headers
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
                    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        session_set_cookie_params([
            'lifetime' => 86400,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $is_https,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }
}

// 3. XSS Protection: HTML Escaping helper
function e(?string $value): string {
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// 4. Input Sanitization
function sanitize_text(?string $data): string {
    if ($data === null) {
        return '';
    }
    return trim(strip_tags($data));
}

// 5. CSRF Token Generator
function generate_csrf_token(): string {
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 6. CSRF Form Field HTML Helper
function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

// 7. CSRF Verification helper
function verify_csrf_token(?string $token): bool {
    start_secure_session();
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// 8. Auth State Helpers
function is_logged_in(): bool {
    start_secure_session();
    return isset($_SESSION['user_id']) && !empty($_SESSION['username']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header("Location: login.php?error=auth_required");
        exit;
    }
}

function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email'    => $_SESSION['user_email'] ?? '',
        'name'     => $_SESSION['user_name'] ?? 'Admin'
    ];
}

// Initialize headers and session automatically
set_security_headers();
start_secure_session();
