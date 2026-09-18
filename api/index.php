<?php
/**
 * Vercel Serverless Entry Router
 * Portfolio Project - Jairus John Valdez
 */

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH) ?? '/';
$clean = trim($path, '/');

// Route mapping
if ($clean === '' || $clean === 'index' || $clean === 'index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// Legal route aliases
$aliases = [
    'privacy-policy'       => 'privacy.php',
    'privacy'              => 'privacy.php',
    'terms'                => 'terms.php',
    'terms-and-conditions' => 'terms.php',
    'terms-of-service'     => 'terms.php',
    'cookie-policy'        => 'cookies.php',
    'cookies'              => 'cookies.php',
];
if (isset($aliases[$clean])) {
    require __DIR__ . '/../' . $aliases[$clean];
    exit;
}

$direct_file = __DIR__ . '/../' . $clean;
if (file_exists($direct_file) && is_file($direct_file) && str_ends_with($clean, '.php')) {
    require $direct_file;
    exit;
}

$php_file = __DIR__ . '/../' . $clean . '.php';
if (file_exists($php_file) && is_file($php_file)) {
    require $php_file;
    exit;
}

// Fallback to index if no matching file
require __DIR__ . '/../index.php';
