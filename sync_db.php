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

function get_pg_connection($host, $port, $name, $user, $pass): PDO {
    return new PDO(
        "pgsql:host={$host};port={$port};dbname={$name};sslmode=require",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

function get_mysql_connection($host, $port, $name, $user, $pass): PDO {
    return new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
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

// Main execution flow
$action = 'pull';
if ($is_cli) {
    global $argv;
    if (isset($argv[1])) {
        if ($argv[1] === '--push') $action = 'push';
        elseif ($argv[1] === '--status') $action = 'status';
        elseif ($argv[1] === '--pull') $action = 'pull';
    }
} else {
    $action = $_GET['action'] ?? ($_POST['action'] ?? 'status');
}

$results = [];
$error = null;

try {
    $pg = get_pg_connection($pgHost, $pgPort, $pgName, $pgUser, $pgPass);
    $my = get_mysql_connection($myHost, $myPort, $myName, $myUser, $myPass);

    if ($action === 'pull') {
        $results = sync_supabase_to_mysql($pg, $my);
    } elseif ($action === 'push') {
        if (!$is_cli && !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception("Invalid CSRF token.");
        }
        $results = sync_mysql_to_supabase($my, $pg);
    }

    $statusData = [
        'supabase' => [
            'users'    => $pg->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'resume'   => $pg->query("SELECT COUNT(*) FROM resume")->fetchColumn(),
            'projects' => $pg->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
            'messages' => $pg->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
        ],
        'mysql' => [
            'users'    => $my->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'resume'   => $my->query("SELECT COUNT(*) FROM resume")->fetchColumn(),
            'projects' => $my->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
            'messages' => $my->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
        ]
    ];
} catch (Exception $e) {
    $error = $e->getMessage();
}

if ($is_cli) {
    echo "========================================================\n";
    echo "Portfolio Database Sync Tool (Supabase <-> Local MySQL)\n";
    echo "========================================================\n";
    if ($error) {
        echo "ERROR: {$error}\n";
        exit(1);
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
        printf("%-9s | %-14d | %d\n", $t, $statusData['supabase'][$t], $statusData['mysql'][$t]);
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
        .sync-container { max-width: 800px; margin: 60px auto; padding: 0 20px; }
        .sync-card { background: rgba(18, 26, 43, 0.9); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 32px; backdrop-filter: blur(12px); }
        .table-compare { width: 100%; border-collapse: collapse; margin: 24px 0; }
        .table-compare th, .table-compare td { padding: 14px 18px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .table-compare th { color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-match { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 0.8rem; font-weight: 600; }
        .badge-diff { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 0.8rem; font-weight: 600; }
        .btn-group { display: flex; gap: 14px; margin-top: 24px; flex-wrap: wrap; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="sync-container">
        <div class="sync-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 style="font-size: 1.5rem; margin: 0; color: #38bdf8;">🔄 Database Sync Center</h1>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Dashboard</a>
            </div>
            <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 24px;">
                Keep your <strong>Local MySQL database</strong> and <strong>Supabase Cloud PostgreSQL</strong> perfectly synchronized.
            </p>

            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 14px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($results)): ?>
                <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #6ee7b7; padding: 14px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>Sync Result:</strong>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <?php foreach ($results as $res): ?>
                            <li><?= htmlspecialchars($res) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (isset($statusData)): ?>
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
                                $pgC = (int)$statusData['supabase'][$t]; 
                                $myC = (int)$statusData['mysql'][$t]; 
                                $matches = ($pgC === $myC);
                            ?>
                            <tr>
                                <td style="font-weight: 600; text-transform: capitalize;"><?= $t ?></td>
                                <td><?= $pgC ?> row<?= $pgC === 1 ? '' : 's' ?></td>
                                <td><?= $myC ?> row<?= $myC === 1 ? '' : 's' ?></td>
                                <td>
                                    <?php if ($matches): ?>
                                        <span class="badge-match">✓ In Sync</span>
                                    <?php else: ?>
                                        <span class="badge-diff">⚠️ <?= abs($pgC - $myC) ?> difference</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="btn-group">
                    <form method="POST" action="sync_db.php?action=pull">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Pull latest data from Supabase into Local MySQL? This will update Local MySQL.')">
                            ⬇️ Pull Cloud &rarr; Local MySQL
                        </button>
                    </form>

                    <form method="POST" action="sync_db.php?action=push">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Push Local MySQL data to Supabase Cloud? This will overwrite Cloud records.')">
                            ⬆️ Push Local MySQL &rarr; Cloud
                        </button>
                    </form>

                    <a href="sync_db.php?action=status" class="btn btn-outline-secondary">
                        🔄 Refresh Status
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
