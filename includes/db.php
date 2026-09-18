<?php
/**
 * Secure Database Connection Handler (PDO)
 * Portfolio Project - Jairus John Valdez
 */

require_once __DIR__ . '/../config/database.php';

try {
    if (DB_TYPE === 'pgsql') {
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;sslmode=require",
            DB_HOST,
            DB_PORT,
            DB_NAME
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true, // Required for PgBouncer / Supabase transaction pooling (port 6543)
            PDO::ATTR_PERSISTENT         => false,
        ];
    } else {
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=%s",
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ];

        // Optional SSL options for cloud MySQL providers (TiDB, Aiven, PlanetScale, etc.)
        if (getenv('MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('MYSQL_ATTR_SSL_CA');
        } elseif (getenv('DB_SSL') === 'true' || (getenv('DB_HOST') && getenv('DB_HOST') !== 'localhost' && getenv('DB_HOST') !== '127.0.0.1')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // If the database doesn't exist yet (MySQL error 1049), guide to setup.php
    $driver_code = (int)($e->errorInfo[1] ?? 0);
    if ($driver_code === 1049 || (int)$e->getCode() === 1049) {
        if (!headers_sent()) {
            header("Location: setup.php");
            exit;
        }
    }
    error_log("Database connection error: " . $e->getMessage());
    $is_vercel = !empty(getenv('VERCEL'));
    $db_label = (DB_TYPE === 'pgsql') ? 'PostgreSQL (Supabase)' : 'MySQL';

    if ($is_vercel && DB_HOST === 'localhost') {
        $troubleshooting = "<li>You are running on <strong>Vercel</strong>, but cloud database environment variables are not set.</li>" .
                           "<li>Go to your <strong>Vercel Dashboard &rarr; Project Settings &rarr; Environment Variables</strong> and add: <code>DB_TYPE</code>, <code>DB_HOST</code>, <code>DB_PORT</code>, <code>DB_USER</code>, and <code>DB_PASS</code>.</li>" .
                           "<li>Ensure you have run <code>supabase.sql</code> in your Supabase SQL Editor.</li>";
    } else {
        $troubleshooting = "<li>Ensure MySQL is running in the <strong>XAMPP Control Panel</strong>.</li>" .
                           "<li>If this is your first time setting up, please run <a href='setup.php' style='color:#1d4ed8; font-weight:bold;'>setup.php</a> to create the database.</li>" .
                           "<li>Check credentials in <code>config/database.php</code>.</li>";
    }

    die(
        "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 24px; border: 1px solid #f87171; border-radius: 8px; background: #fff5f5; color: #991b1b;'>" .
        "<h2 style='margin-top:0;'>⚠️ Database Connection Notice</h2>" .
        "<p>A connection to {$db_label} could not be established.</p>" .
        "<p><strong>Details:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>" .
        "<p><strong>Troubleshooting:</strong></p>" .
        "<ul>" . $troubleshooting . "</ul>" .
        "</div>"
    );
}
