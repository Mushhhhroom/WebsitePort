<?php
/**
 * Global Configuration Settings
 * Portfolio Project - Jairus John Valdez
 */

// Load local .env / .env.local if present
$envFiles = [__DIR__ . '/../.env', __DIR__ . '/../.env.local'];
foreach ($envFiles as $envFile) {
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (str_contains($line, '=')) {
                list($name, $val) = explode('=', $line, 2);
                $name = trim($name);
                $val = trim($val, " \t\n\r\0\x0B\"'");
                if (getenv($name) === false) {
                    putenv("{$name}={$val}");
                    $_ENV[$name] = $val;
                }
            }
        }
    }
}

// Support DATABASE_URL / POSTGRES_URL connection strings
$database_url = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');
if ($database_url) {
    $parsed = parse_url($database_url);
    if ($parsed) {
        $scheme = $parsed['scheme'] ?? '';
        $is_pg = (str_starts_with($scheme, 'postgres') || str_starts_with($scheme, 'pgsql'));
        if (getenv('DB_TYPE') === false) {
            putenv('DB_TYPE=' . ($is_pg ? 'pgsql' : 'mysql'));
        }
        if (getenv('DB_HOST') === false && isset($parsed['host'])) {
            putenv('DB_HOST=' . $parsed['host']);
        }
        if (getenv('DB_PORT') === false && isset($parsed['port'])) {
            putenv('DB_PORT=' . (string)$parsed['port']);
        }
        if (getenv('DB_USER') === false && isset($parsed['user'])) {
            putenv('DB_USER=' . urldecode($parsed['user']));
        }
        if (getenv('DB_PASS') === false && isset($parsed['pass'])) {
            putenv('DB_PASS=' . urldecode($parsed['pass']));
        }
        if (getenv('DB_NAME') === false && isset($parsed['path'])) {
            putenv('DB_NAME=' . ltrim($parsed['path'], '/'));
        }
    }
}

$is_vercel = !empty(getenv('VERCEL')) || !empty(getenv('NOW_REGION'));

// Supabase cloud credentials fallback
$default_type = $is_vercel ? 'pgsql' : 'mysql';
$default_host = $is_vercel ? 'aws-0-ap-northeast-2.pooler.supabase.com' : 'localhost';
$default_port = $is_vercel ? '6543' : '3306';
$default_name = $is_vercel ? 'postgres' : 'portfolio';
$default_user = $is_vercel ? 'postgres.rmvsmuwibadtuswbmphq' : 'root';
$default_pass = $is_vercel ? '09062126799Mushroom_po28' : '';

// Database Driver & Connection Settings
$db_host = getenv('DB_HOST') ?: $default_host;

// Detect PostgreSQL / Supabase vs MySQL
$db_type = getenv('DB_TYPE');
if (!$db_type) {
    if (str_contains($db_host, 'supabase.co') || str_contains($db_host, 'pooler.supabase.com') || getenv('DB_PORT') == '5432' || getenv('DB_PORT') == '6543') {
        $db_type = 'pgsql';
    } else {
        $db_type = $default_type;
    }
}

define('DB_TYPE', $db_type);
define('DB_HOST', $db_host);
define('DB_PORT', getenv('DB_PORT') ?: ($db_type === 'pgsql' ? ($is_vercel ? $default_port : '6543') : '3306'));
define('DB_NAME', getenv('DB_NAME') ?: ($db_type === 'pgsql' ? $default_name : 'portfolio'));
define('DB_USER', getenv('DB_USER') ?: ($db_type === 'pgsql' ? $default_user : 'root'));
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : $default_pass);
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// Site Metadata
define('SITE_NAME', 'Jairus John D. Valdez | Portfolio');
define('OWNER_NAME', 'Jairus John D. Valdez');
define('OWNER_ROLE', 'Fourth-Year BS Computer Science Student | Website, Application & Mobile Developer');
define('CONTACT_EMAIL', 'Valdez.jairusjohn.deleste@gmail.com');

// Session & Security
define('SESSION_LIFETIME', 86400); // 24 hours
