<?php
/**
 * Portfolio System Setup & Database Migrator
 * Run via CLI: php setup.php
 * Or visit in browser: http://localhost:8000/setup.php
 */

$is_cli = (php_sapi_name() === 'cli');

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
}

$is_local_mysql = (!defined('DB_TYPE') || DB_TYPE === 'mysql');
$db_host = ($is_local_mysql && defined('DB_HOST')) ? DB_HOST : 'localhost';
$db_port = ($is_local_mysql && defined('DB_PORT')) ? DB_PORT : '3306';
$db_user = ($is_local_mysql && defined('DB_USER')) ? DB_USER : 'root';
$db_pass = ($is_local_mysql && defined('DB_PASS')) ? DB_PASS : '';
$db_name = ($is_local_mysql && defined('DB_NAME')) ? DB_NAME : 'portfolio';

$messages = [];
$success = true;

try {
    // 1. Connect to MySQL server without selecting database first
    $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $messages[] = "Connected to MySQL server on {$db_host}:{$db_port}.";

    // 2. Read database.sql
    $sql_file = __DIR__ . '/database.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("Schema file database.sql not found at {$sql_file}");
    }

    $sql_content = file_get_contents($sql_file);

    // 3. Execute queries
    $pdo->exec($sql_content);
    $messages[] = "Executed database.sql: Database '{$db_name}' and tables created/verified successfully.";

    // 4. Verify tables
    $pdo->exec("USE `{$db_name}`");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $messages[] = "Active tables in '{$db_name}': " . implode(', ', $tables);

    // 5. Ensure Default Admin User has verified bcrypt hash
    $default_pass = 'password123';
    $admin_hash = password_hash($default_pass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET password = ?, email = 'Valdez.jairusjohn.deleste@gmail.com', full_name = 'Jairus John D. Valdez',
                headline = 'Fourth-Year BS Computer Science Student | Website, Application & Mobile Developer'
            WHERE id = ?
        ");
        $updateStmt->execute([$admin_hash, $admin['id']]);
        $messages[] = "Default Admin verified: Username: 'admin' | Email: 'Valdez.jairusjohn.deleste@gmail.com' | Default Password: '{$default_pass}' (hash refreshed)";
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO users (username, password, email, full_name, headline, bio, github, linkedin)
            VALUES ('admin', ?, 'Valdez.jairusjohn.deleste@gmail.com', 'Jairus John D. Valdez', 
                    'Fourth-Year BS Computer Science Student | Website, Application & Mobile Developer',
                    'Highly motivated Fourth-Year Bachelor of Science in Computer Science student at Quezon City University. Possesses a strong technical foundation in system troubleshooting and digital platforms, combined with a professional approach to problem-solving.',
                    'https://github.com/Mushhhhroom', 'https://www.linkedin.com/in/jairus-valdez-19469a313/')
        ");
        $insertStmt->execute([$admin_hash]);
        $messages[] = "Default Admin created: Username: 'admin' | Default Password: '{$default_pass}'";
    }

} catch (Exception $e) {
    $success = false;
    $messages[] = "ERROR: " . $e->getMessage();
}

if ($is_cli) {
    echo "==============================================\n";
    echo "Portfolio Database Setup Script\n";
    echo "==============================================\n";
    foreach ($messages as $msg) {
        echo "[*] {$msg}\n";
    }
    echo "==============================================\n";
    echo $success ? "STATUS: SUCCESS!\n" : "STATUS: FAILED!\n";
    exit($success ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio System Installer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0d1117;
            color: #c9d1d9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 32px;
            max-width: 650px;
            width: 100%;
            box-shadow: 0 16px 36px rgba(0,0,0,0.5);
        }
        h1 { color: #58a6ff; margin-top: 0; font-size: 1.6rem; }
        .log {
            background: #090d13;
            border: 1px solid #21262d;
            border-radius: 8px;
            padding: 16px;
            font-family: monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 20px 0;
        }
        .log p { margin: 6px 0; }
        .success { color: #3fb950; font-weight: bold; }
        .error { color: #f85149; font-weight: bold; }
        .btn {
            display: inline-block;
            background: #238636;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            margin-right: 12px;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #30363d;
            color: #58a6ff;
        }
        .credentials-box {
            background: #1c2128;
            border-left: 4px solid #58a6ff;
            padding: 14px 18px;
            margin: 18px 0;
            border-radius: 0 6px 6px 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 Portfolio System Installation</h1>
        <p>Database initialization report for Jairus John Valdez's Portfolio:</p>
        
        <div class="log">
            <?php foreach ($messages as $msg): ?>
                <p><?php echo htmlspecialchars($msg); ?></p>
            <?php endforeach; ?>
        </div>

        <?php if ($success): ?>
            <div class="credentials-box">
                <strong>Default Administrator Credentials:</strong><br>
                Username: <code>admin</code><br>
                Password: <code>password123</code><br>
                <em>(Make sure to change your password in the dashboard after logging in!)</em>
            </div>
            <a href="index.php" class="btn">View Website</a>
            <a href="login.php" class="btn btn-outline">Admin Login</a>
        <?php else: ?>
            <p class="error">Setup encountered an error. Please verify your MySQL credentials in setup.php.</p>
        <?php endif; ?>
    </div>
</body>
</html>
