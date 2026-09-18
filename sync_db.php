<?php
/**
 * Database Synchronization Utility (Supabase Cloud <-> Local MySQL)
 * Portfolio Project - Jairus John Valdez
 * 
 * Usage via CLI:
 *   php sync_db.php          # Pulls Supabase data into Local MySQL (default)
 *   php sync_db.php --pull   # Pulls Supabase data into Local MySQL
 *   php sync_db.php --push   # Pushes Local MySQL data to Supabase
 *   php sync_db.php --status # Checks record counts on both databases
 * 
 * Usage via Browser:
 *   Visit http://localhost/portfolio/sync_db.php (Admin login required)
 *   or view Cloud Database Status on Vercel
 */

$is_cli = (php_sapi_name() === 'cli');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

// If running in browser, require admin login for security
if (!$is_cli) {
    if (!is_logged_in()) {
        header('Location: login.php?error=auth_required');
        exit;
    }
}

// Environment Detection
$is_vercel = !empty(getenv('VERCEL')) || !empty(getenv('NOW_REGION')) || !empty($_SERVER['VERCEL']) || (defined('DB_TYPE') && DB_TYPE === 'pgsql' && !empty(getenv('VERCEL')));

// Supabase Connection Credentials
$pgHost = getenv('SUPABASE_HOST') ?: 'aws-0-ap-northeast-2.pooler.supabase.com';
$pgPort = getenv('SUPABASE_PORT') ?: '6543';
$pgName = getenv('SUPABASE_NAME') ?: 'postgres';
$pgUser = getenv('SUPABASE_USER') ?: 'postgres.rmvsmuwibadtuswbmphq';
$pgPass = getenv('SUPABASE_PASS') ?: '09062126799Mushroom_po28';

// Local MySQL Connection Credentials
$myHost = getenv('DB_HOST') && getenv('DB_TYPE') === 'mysql' ? getenv('DB_HOST') : 'localhost';
$myPort = getenv('DB_PORT') && getenv('DB_TYPE') === 'mysql' ? getenv('DB_PORT') : '3306';
$myName = getenv('DB_NAME') && getenv('DB_TYPE') === 'mysql' ? getenv('DB_NAME') : 'portfolio';
$myUser = getenv('DB_USER') && getenv('DB_TYPE') === 'mysql' ? getenv('DB_USER') : 'root';
$myPass = getenv('DB_PASS') !== false && getenv('DB_TYPE') === 'mysql' ? getenv('DB_PASS') : '';

function get_pg_connection($host, $port, $name, $user, $pass): ?PDO {
    if (!extension_loaded('pdo_pgsql')) {
        throw new Exception("PHP extension 'pdo_pgsql' is not enabled.");
    }
    return new PDO(
        "pgsql:host={$host};port={$port};dbname={$name};sslmode=require",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 5
        ]
    );
}

function get_mysql_connection($host, $port, $name, $user, $pass): ?PDO {
    if (!extension_loaded('pdo_mysql')) {
        throw new Exception("PHP extension 'pdo_mysql' is not enabled.");
    }
    // On Unix/Linux, using 'localhost' causes PDO to use local unix socket files (/tmp/mysql.sock)
    // Using 127.0.0.1 forces TCP connection to port 3306
    if ($host === 'localhost' && DIRECTORY_SEPARATOR === '/') {
        $host = '127.0.0.1';
    }
    return new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 2
        ]
    );
}

function sync_supabase_to_mysql(PDO $pg, PDO $my): array {
    $log = [];
    $my->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 1. Users
    $users = $pg->query("SELECT * FROM users ORDER BY id")->fetchAll();
    $my->exec("TRUNCATE TABLE users");
    $uStmt = $my->prepare("
        INSERT INTO users (id, username, password, email, full_name, headline, bio, github, linkedin, created_at, updated_at)
        VALUES (:id, :username, :password, :email, :full_name, :headline, :bio, :github, :linkedin, :created_at, :updated_at)
    ");
    foreach ($users as $u) {
        $uStmt->execute([
            ':id'         => $u['id'],
            ':username'   => $u['username'],
            ':password'   => $u['password'],
            ':email'      => $u['email'],
            ':full_name'  => $u['full_name'],
            ':headline'   => $u['headline'],
            ':bio'        => $u['bio'],
            ':github'     => $u['github'],
            ':linkedin'   => $u['linkedin'],
            ':created_at' => date('Y-m-d H:i:s', strtotime($u['created_at'])),
            ':updated_at' => date('Y-m-d H:i:s', strtotime($u['updated_at'] ?? $u['created_at'])),
        ]);
    }
    $log[] = "Users synced: " . count($users) . " record(s)";

    // 2. Resume
    $resumes = $pg->query("SELECT * FROM resume ORDER BY section, display_order, id")->fetchAll();
    $my->exec("TRUNCATE TABLE resume");
    $rStmt = $my->prepare("
        INSERT INTO resume (id, section, title, subtitle, date_range, content, display_order, created_at)
        VALUES (:id, :section, :title, :subtitle, :date_range, :content, :display_order, :created_at)
    ");
    foreach ($resumes as $r) {
        $rStmt->execute([
            ':id'            => $r['id'],
            ':section'       => $r['section'],
            ':title'         => $r['title'],
            ':subtitle'      => $r['subtitle'],
            ':date_range'    => $r['date_range'],
            ':content'       => $r['content'],
            ':display_order' => $r['display_order'],
            ':created_at'    => date('Y-m-d H:i:s', strtotime($r['created_at'])),
        ]);
    }
    $log[] = "Resume entries synced: " . count($resumes) . " record(s)";

    // 3. Projects
    $projects = $pg->query("SELECT * FROM projects ORDER BY id")->fetchAll();
    $my->exec("TRUNCATE TABLE projects");
    $pStmt = $my->prepare("
        INSERT INTO projects (id, title, description, tech_stack, category, github_link, demo_link, featured, created_at, updated_at)
        VALUES (:id, :title, :description, :tech_stack, :category, :github_link, :demo_link, :featured, :created_at, :updated_at)
    ");
    foreach ($projects as $p) {
        $pStmt->execute([
            ':id'          => $p['id'],
            ':title'       => $p['title'],
            ':description' => $p['description'],
            ':tech_stack'  => $p['tech_stack'],
            ':category'    => $p['category'],
            ':github_link' => $p['github_link'],
            ':demo_link'   => $p['demo_link'],
            ':featured'    => (int)$p['featured'],
            ':created_at'  => date('Y-m-d H:i:s', strtotime($p['created_at'])),
            ':updated_at'  => date('Y-m-d H:i:s', strtotime($p['updated_at'] ?? $p['created_at'])),
        ]);
    }
    $log[] = "Projects synced: " . count($projects) . " record(s)";

    // 4. Messages
    $messages = $pg->query("SELECT * FROM messages ORDER BY id")->fetchAll();
    $my->exec("TRUNCATE TABLE messages");
    $mStmt = $my->prepare("
        INSERT INTO messages (id, name, email, subject, message, ip_address, is_read, created_at)
        VALUES (:id, :name, :email, :subject, :message, :ip_address, :is_read, :created_at)
    ");
    foreach ($messages as $m) {
        $mStmt->execute([
            ':id'         => $m['id'],
            ':name'       => $m['name'],
            ':email'      => $m['email'],
            ':subject'    => $m['subject'],
            ':message'    => $m['message'],
            ':ip_address' => $m['ip_address'],
            ':is_read'    => (int)$m['is_read'],
            ':created_at' => date('Y-m-d H:i:s', strtotime($m['created_at'])),
        ]);
    }
    $log[] = "Messages synced: " . count($messages) . " record(s)";

    $my->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Reset auto-increment
    $my->exec("ALTER TABLE users AUTO_INCREMENT = " . ((int)$my->query("SELECT COALESCE(MAX(id), 0) + 1 FROM users")->fetchColumn()));
    $my->exec("ALTER TABLE resume AUTO_INCREMENT = " . ((int)$my->query("SELECT COALESCE(MAX(id), 0) + 1 FROM resume")->fetchColumn()));
    $my->exec("ALTER TABLE projects AUTO_INCREMENT = " . ((int)$my->query("SELECT COALESCE(MAX(id), 0) + 1 FROM projects")->fetchColumn()));
    $my->exec("ALTER TABLE messages AUTO_INCREMENT = " . ((int)$my->query("SELECT COALESCE(MAX(id), 0) + 1 FROM messages")->fetchColumn()));

    return $log;
}

function sync_mysql_to_supabase(PDO $my, PDO $pg): array {
    $log = [];

    // 1. Users
    $users = $my->query("SELECT * FROM users ORDER BY id")->fetchAll();
    $pg->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE");
    $uStmt = $pg->prepare("
        INSERT INTO users (id, username, password, email, full_name, headline, bio, github, linkedin, created_at, updated_at)
        VALUES (:id, :username, :password, :email, :full_name, :headline, :bio, :github, :linkedin, :created_at, :updated_at)
    ");
    foreach ($users as $u) {
        $uStmt->execute([
            ':id'         => $u['id'],
            ':username'   => $u['username'],
            ':password'   => $u['password'],
            ':email'      => $u['email'],
            ':full_name'  => $u['full_name'],
            ':headline'   => $u['headline'],
            ':bio'        => $u['bio'],
            ':github'     => $u['github'],
            ':linkedin'   => $u['linkedin'],
            ':created_at' => $u['created_at'],
            ':updated_at' => $u['updated_at'],
        ]);
    }
    $log[] = "Users pushed to Supabase: " . count($users) . " record(s)";

    // 2. Resume
    $resumes = $my->query("SELECT * FROM resume ORDER BY section, display_order, id")->fetchAll();
    $pg->exec("TRUNCATE TABLE resume RESTART IDENTITY CASCADE");
    $rStmt = $pg->prepare("
        INSERT INTO resume (id, section, title, subtitle, date_range, content, display_order, created_at)
        VALUES (:id, :section, :title, :subtitle, :date_range, :content, :display_order, :created_at)
    ");
    foreach ($resumes as $r) {
        $rStmt->execute([
            ':id'            => $r['id'],
            ':section'       => $r['section'],
            ':title'         => $r['title'],
            ':subtitle'      => $r['subtitle'],
            ':date_range'    => $r['date_range'],
            ':content'       => $r['content'],
            ':display_order' => $r['display_order'],
            ':created_at'    => $r['created_at'],
        ]);
    }
    $log[] = "Resume pushed to Supabase: " . count($resumes) . " record(s)";

    // 3. Projects
    $projects = $my->query("SELECT * FROM projects ORDER BY id")->fetchAll();
    $pg->exec("TRUNCATE TABLE projects RESTART IDENTITY CASCADE");
    $pStmt = $pg->prepare("
        INSERT INTO projects (id, title, description, tech_stack, category, github_link, demo_link, featured, created_at, updated_at)
        VALUES (:id, :title, :description, :tech_stack, :category, :github_link, :demo_link, :featured, :created_at, :updated_at)
    ");
    foreach ($projects as $p) {
        $pStmt->execute([
            ':id'          => $p['id'],
            ':title'       => $p['title'],
            ':description' => $p['description'],
            ':tech_stack'  => $p['tech_stack'],
            ':category'    => $p['category'],
            ':github_link' => $p['github_link'],
            ':demo_link'   => $p['demo_link'],
            ':featured'    => (int)$p['featured'],
            ':created_at'  => $p['created_at'],
            ':updated_at'  => $p['updated_at'],
        ]);
    }
    $log[] = "Projects pushed to Supabase: " . count($projects) . " record(s)";

    // 4. Messages
    $messages = $my->query("SELECT * FROM messages ORDER BY id")->fetchAll();
    $pg->exec("TRUNCATE TABLE messages RESTART IDENTITY CASCADE");
    $mStmt = $pg->prepare("
        INSERT INTO messages (id, name, email, subject, message, ip_address, is_read, created_at)
        VALUES (:id, :name, :email, :subject, :message, :ip_address, :is_read, :created_at)
    ");
    foreach ($messages as $m) {
        $mStmt->execute([
            ':id'         => $m['id'],
            ':name'       => $m['name'],
            ':email'      => $m['email'],
            ':subject'    => $m['subject'],
            ':message'    => $m['message'],
            ':ip_address' => $m['ip_address'],
            ':is_read'    => (int)$m['is_read'],
            ':created_at' => $m['created_at'],
        ]);
    }
    $log[] = "Messages pushed to Supabase: " . count($messages) . " record(s)";

    return $log;
}

// Independent Database Connections
$pg = null;
$my = null;
$pg_error = null;
$my_error = null;

// 1. Probe Supabase Cloud
try {
    $pg = get_pg_connection($pgHost, $pgPort, $pgName, $pgUser, $pgPass);
} catch (Exception $e) {
    $pg_error = $e->getMessage();
}

// 2. Probe Local MySQL
// On Vercel / Cloud serverless without a remote MySQL host, local MySQL is not on the Lambda instance
if ($is_vercel && ($myHost === 'localhost' || $myHost === '127.0.0.1')) {
    $my_error = "Local MySQL is running on your private workstation and cannot be reached from Vercel's cloud serverless containers.";
} else {
    try {
        $my = get_mysql_connection($myHost, $myPort, $myName, $myUser, $myPass);
    } catch (Exception $e) {
        $my_error = $e->getMessage();
    }
}

// Action Controller
$action = 'status';
if ($is_cli) {
    global $argv;
    if (isset($argv[1])) {
        if ($argv[1] === '--push') $action = 'push';
        elseif ($argv[1] === '--status') $action = 'status';
        elseif ($argv[1] === '--pull') $action = 'pull';
    } else {
        $action = 'pull';
    }
} else {
    $action = $_GET['action'] ?? ($_POST['action'] ?? 'status');
}

$results = [];
$error = null;

if ($action === 'pull' || $action === 'push') {
    if (!$pg) {
        $error = "Cannot synchronize: Supabase Cloud PostgreSQL is unreachable (" . ($pg_error ?? 'Connection error') . ").";
    } elseif (!$my) {
        $error = "Cannot synchronize from this cloud environment: Local MySQL is unreachable (" . ($my_error ?? 'Offline') . "). To synchronize databases, run the CLI utility directly from your local computer terminal: php sync_db.php --" . $action;
    } else {
        if ($action === 'push' && !$is_cli && !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $error = "Invalid or expired security token (CSRF). Please refresh the page and try again.";
        } else {
            try {
                if ($action === 'pull') {
                    $results = sync_supabase_to_mysql($pg, $my);
                } else {
                    $results = sync_mysql_to_supabase($my, $pg);
                }
            } catch (Exception $e) {
                $error = "Synchronization error: " . $e->getMessage();
            }
        }
    }
}

// Retrieve Table Counts
$statusData = [
    'supabase' => null,
    'mysql'    => null
];

if ($pg) {
    try {
        $statusData['supabase'] = [
            'users'    => (int)$pg->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'resume'   => (int)$pg->query("SELECT COUNT(*) FROM resume")->fetchColumn(),
            'projects' => (int)$pg->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
            'messages' => (int)$pg->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
        ];
    } catch (Exception $e) {
        $pg_error = "Failed to query Supabase tables: " . $e->getMessage();
    }
}

if ($my) {
    try {
        $statusData['mysql'] = [
            'users'    => (int)$my->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'resume'   => (int)$my->query("SELECT COUNT(*) FROM resume")->fetchColumn(),
            'projects' => (int)$my->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
            'messages' => (int)$my->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
        ];
    } catch (Exception $e) {
        $my_error = "Failed to query MySQL tables: " . $e->getMessage();
    }
}

// Handle CLI Output
if ($is_cli) {
    echo "========================================================\n";
    echo "Portfolio Database Sync Tool (Supabase <-> Local MySQL)\n";
    echo "========================================================\n";
    if ($error) {
        echo "ERROR: {$error}\n";
        exit(1);
    }
    if ($pg_error) {
        echo "[!] Supabase Warning: {$pg_error}\n";
    }
    if ($my_error) {
        echo "[!] MySQL Warning: {$my_error}\n";
    }
    if (!empty($results)) {
        echo "Action [{$action}]:\n";
        foreach ($results as $res) {
            echo "  [OK] {$res}\n";
        }
        echo "\n";
    }
    echo "Current Record Counts:\n";
    echo "Table     | Supabase Cloud | Local MySQL\n";
    echo "----------+----------------+------------\n";
    foreach (['users', 'resume', 'projects', 'messages'] as $t) {
        $pgVal = isset($statusData['supabase'][$t]) ? (string)$statusData['supabase'][$t] : 'ERROR';
        $myVal = isset($statusData['mysql'][$t]) ? (string)$statusData['mysql'][$t] : 'OFFLINE';
        printf("%-9s | %-14s | %s\n", $t, $pgVal, $myVal);
    }
    echo "========================================================\n";
    echo "Synchronization complete!\n";
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Sync Tool | Jairus John Valdez</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .sync-container { max-width: 860px; margin: 40px auto 80px; padding: 0 20px; }
        .sync-card { background: rgba(18, 26, 43, 0.95); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 16px; padding: 32px; backdrop-filter: blur(14px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); }
        .db-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 20px 0; }
        @media (max-width: 700px) { .db-grid { grid-template-columns: 1fr; } }
        .db-badge-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 18px; }
        .table-compare { width: 100%; border-collapse: collapse; margin: 24px 0; }
        .table-compare th, .table-compare td { padding: 14px 18px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .table-compare th { color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-match { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 0.8rem; font-weight: 600; }
        .badge-diff { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 0.8rem; font-weight: 600; }
        .badge-cloud { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-size: 0.8rem; font-weight: 600; }
        .btn-group { display: flex; gap: 14px; margin-top: 24px; flex-wrap: wrap; }
        .cli-codebox { background: #0b1120; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 14px 18px; font-family: var(--font-mono, monospace); font-size: 0.85rem; color: #38bdf8; margin-top: 12px; line-height: 1.6; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="sync-container">
        <div class="sync-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <span class="tag-badge <?= $is_vercel ? 'tag-badge-cyan' : 'tag-badge-green' ?>" style="margin-bottom: 6px; display: inline-block;">
                        <?= $is_vercel ? '☁️ Cloud Serverless (Vercel)' : '💻 Local Workstation Environment' ?>
                    </span>
                    <h1 style="font-size: 1.6rem; margin: 4px 0 0; color: #38bdf8; font-weight: 800;">
                        🔄 Database Sync Center
                    </h1>
                </div>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm" style="padding: 8px 16px; font-weight: 600;">
                    &larr; Back to Dashboard
                </a>
            </div>

            <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 20px;">
                Keep your <strong>Local MySQL database</strong> and <strong>Supabase Cloud PostgreSQL</strong> perfectly synchronized.
            </p>

            <!-- Database Health Cards -->
            <div class="db-grid">
                <!-- Supabase Cloud Card -->
                <div class="db-badge-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: #e2e8f0; font-size: 1rem;">☁️ Supabase Cloud (PostgreSQL)</strong>
                        <?php if ($pg): ?>
                            <span class="badge-match">● Connected</span>
                        <?php else: ?>
                            <span class="badge-diff">● Unreachable</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.85rem; color: #94a3b8; font-family: monospace;">
                        Host: <?= htmlspecialchars($pgHost) ?><br>
                        Status: <?= $pg ? 'SSL Secure Connection Established' : htmlspecialchars($pg_error ?? 'Offline') ?>
                    </div>
                </div>

                <!-- Local MySQL Card -->
                <div class="db-badge-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: #e2e8f0; font-size: 1rem;">💻 Local MySQL</strong>
                        <?php if ($my): ?>
                            <span class="badge-match">● Connected</span>
                        <?php elseif ($is_vercel): ?>
                            <span class="badge-cloud">☁️ Local Only</span>
                        <?php else: ?>
                            <span class="badge-diff">● Offline</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.85rem; color: #94a3b8; font-family: monospace;">
                        Host: <?= htmlspecialchars($myHost) ?>:<?= htmlspecialchars($myPort) ?><br>
                        Status: <?= $my ? 'Connected (TCP/Port 3306)' : ($is_vercel ? 'Runs on local PC (Not on Vercel)' : htmlspecialchars($my_error ?? 'Offline')) ?>
                    </div>
                </div>
            </div>

            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 16px; border-radius: 10px; margin-bottom: 24px;">
                    <strong>Notice:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($results)): ?>
                <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #6ee7b7; padding: 16px; border-radius: 10px; margin-bottom: 24px;">
                    <strong>Sync Execution Succeeded:</strong>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <?php foreach ($results as $res): ?>
                            <li><?= htmlspecialchars($res) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Table Comparison Section -->
            <?php if ($statusData['supabase'] !== null || $statusData['mysql'] !== null): ?>
                <table class="table-compare">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Supabase (Cloud)</th>
                            <th>Local MySQL</th>
                            <th>Sync Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (['users', 'resume', 'projects', 'messages'] as $t): ?>
                            <?php 
                                $pgC = $statusData['supabase'][$t] ?? null; 
                                $myC = $statusData['mysql'][$t] ?? null; 
                                $bothOnline = ($pgC !== null && $myC !== null);
                                $matches = ($bothOnline && (int)$pgC === (int)$myC);
                            ?>
                            <tr>
                                <td style="font-weight: 600; text-transform: capitalize;"><?= $t ?></td>
                                <td>
                                    <?= $pgC !== null ? ((int)$pgC . ' row' . ((int)$pgC === 1 ? '' : 's')) : '<span style="color: #ef4444;">Unreachable</span>' ?>
                                </td>
                                <td>
                                    <?php if ($myC !== null): ?>
                                        <?= (int)$myC ?> row<?= (int)$myC === 1 ? '' : 's' ?>
                                    <?php elseif ($is_vercel): ?>
                                        <span style="color: #94a3b8; font-size: 0.85rem;">Local Workstation</span>
                                    <?php else: ?>
                                        <span style="color: #ef4444; font-size: 0.85rem;">Unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($bothOnline): ?>
                                        <?php if ($matches): ?>
                                            <span class="badge-match">✓ In Sync</span>
                                        <?php else: ?>
                                            <span class="badge-diff">⚠️ <?= abs((int)$pgC - (int)$myC) ?> difference</span>
                                        <?php endif; ?>
                                    <?php elseif ($is_vercel && $pgC !== null): ?>
                                        <span class="badge-cloud">✓ Active in Cloud</span>
                                    <?php else: ?>
                                        <span class="badge-diff">Database Disconnected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Action Controls -->
            <div class="btn-group">
                <?php if ($my && $pg): ?>
                    <form method="POST" action="sync_db.php?action=pull">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Pull latest data from Supabase into Local MySQL? This will update your Local MySQL database.')">
                            ⬇️ Pull Cloud &rarr; Local MySQL
                        </button>
                    </form>

                    <form method="POST" action="sync_db.php?action=push">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Push Local MySQL data to Supabase Cloud? This will overwrite Cloud records.')">
                            ⬆️ Push Local MySQL &rarr; Cloud
                        </button>
                    </form>
                <?php endif; ?>

                <a href="sync_db.php?action=status" class="btn btn-outline-secondary">
                    🔄 Refresh Status
                </a>
            </div>

            <!-- Serverless Cloud Guidance Card -->
            <?php if ($is_vercel): ?>
                <div style="margin-top: 32px; background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 12px; padding: 22px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <span style="font-size: 1.4rem;">💡</span>
                        <strong style="color: #38bdf8; font-size: 1.05rem;">How Synchronization Works on Vercel</strong>
                    </div>
                    <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0 0 12px 0;">
                        This live website on Vercel is connected directly to your <strong>Supabase Cloud PostgreSQL</strong> database, so any updates made here are saved in the cloud instantly.
                        Because your <strong>Local MySQL</strong> database lives securely on your local computer, synchronization between Cloud and Local is run from your computer's terminal:
                    </p>
                    <div class="cli-codebox">
                        # Pull all latest Cloud records into your Local MySQL:<br>
                        <span style="color: #a7f3d0;">php sync_db.php --pull</span><br><br>
                        # Push any Local MySQL edits up to Supabase Cloud:<br>
                        <span style="color: #fde68a;">php sync_db.php --push</span><br><br>
                        # Check status and compare record counts:<br>
                        <span style="color: #bae6fd;">php sync_db.php --status</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
