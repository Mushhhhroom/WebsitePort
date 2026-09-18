<?php
/**
 * Cloud Synchronization & Multi-Database Mirroring Handler
 * Portfolio Project - Jairus John Valdez
 * 
 * Ensures that changes made locally (projects, resume, profile, password, messages)
 * are seamlessly mirrored to Supabase Cloud, keeping both databases updated and
 * ensuring account access runs properly across both Localhost and Vercel.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Obtain a PDO connection to Supabase Cloud PostgreSQL
 */
function get_cloud_pdo(): ?PDO {
    if (!extension_loaded('pdo_pgsql')) {
        return null;
    }

    $pgHost = getenv('SUPABASE_HOST') ?: 'aws-0-ap-northeast-2.pooler.supabase.com';
    $pgPort = getenv('SUPABASE_PORT') ?: '6543';
    $pgName = getenv('SUPABASE_NAME') ?: 'postgres';
    $pgUser = getenv('SUPABASE_USER') ?: 'postgres.rmvsmuwibadtuswbmphq';
    $pgPass = getenv('SUPABASE_PASS') ?: '09062126799Mushroom_po28';

    try {
        $pdo = new PDO(
            "pgsql:host={$pgHost};port={$pgPort};dbname={$pgName};sslmode=require",
            $pgUser,
            $pgPass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT            => 5,
                PDO::ATTR_EMULATE_PREPARES   => true,
                PDO::ATTR_PERSISTENT         => false,
            ]
        );
        return $pdo;
    } catch (Exception $e) {
        error_log("Cloud DB connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Mirror User/Profile/Password changes to Supabase Cloud
 */
function mirror_user_update_to_cloud(int $userId, array $data): bool {
    // If already running on pgsql (e.g., live on Vercel), it's already on cloud
    if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
        return true;
    }

    $cloud = get_cloud_pdo();
    if (!$cloud) {
        return false;
    }

    try {
        if (!empty($data['password'])) {
            $stmt = $cloud->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE username = 'admin' OR id = ?");
            $stmt->execute([$data['password'], $userId]);
        }

        if (isset($data['full_name'])) {
            $stmt = $cloud->prepare("
                UPDATE users 
                SET full_name = ?, headline = ?, email = ?, bio = ?, github = ?, linkedin = ?, updated_at = NOW()
                WHERE username = 'admin' OR id = ?
            ");
            $stmt->execute([
                $data['full_name'],
                $data['headline'],
                $data['email'],
                $data['bio'] ?? '',
                $data['github'] ?? '',
                $data['linkedin'] ?? '',
                $userId
            ]);
        }
        return true;
    } catch (Exception $e) {
        error_log("mirror_user_update_to_cloud error: " . $e->getMessage());
        return false;
    }
}

/**
 * Mirror Project Add/Edit/Delete to Supabase Cloud
 */
function mirror_project_to_cloud(string $action, array $data): bool {
    if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
        return true;
    }

    $cloud = get_cloud_pdo();
    if (!$cloud) {
        return false;
    }

    try {
        if ($action === 'add') {
            $stmt = $cloud->prepare("
                INSERT INTO projects (title, description, tech_stack, category, github_link, demo_link, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['tech_stack'],
                $data['category'],
                !empty($data['github_link']) ? $data['github_link'] : null,
                !empty($data['demo_link']) ? $data['demo_link'] : null,
                (int)($data['featured'] ?? 0)
            ]);
        } elseif ($action === 'edit') {
            $stmt = $cloud->prepare("
                UPDATE projects 
                SET title = ?, description = ?, tech_stack = ?, category = ?, github_link = ?, demo_link = ?, featured = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['tech_stack'],
                $data['category'],
                !empty($data['github_link']) ? $data['github_link'] : null,
                !empty($data['demo_link']) ? $data['demo_link'] : null,
                (int)($data['featured'] ?? 0),
                (int)$data['id']
            ]);
        } elseif ($action === 'delete') {
            $stmt = $cloud->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([(int)$data['id']]);
        }
        return true;
    } catch (Exception $e) {
        error_log("mirror_project_to_cloud error: " . $e->getMessage());
        return false;
    }
}

/**
 * Mirror Resume Add/Delete to Supabase Cloud
 */
function mirror_resume_to_cloud(string $action, array $data): bool {
    if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
        return true;
    }

    $cloud = get_cloud_pdo();
    if (!$cloud) {
        return false;
    }

    try {
        if ($action === 'add') {
            $stmt = $cloud->prepare("
                INSERT INTO resume (section, title, subtitle, date_range, content, display_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $data['section'],
                $data['title'],
                !empty($data['subtitle']) ? $data['subtitle'] : null,
                !empty($data['date_range']) ? $data['date_range'] : null,
                $data['content'],
                (int)($data['display_order'] ?? 0)
            ]);
        } elseif ($action === 'delete') {
            $stmt = $cloud->prepare("DELETE FROM resume WHERE id = ?");
            $stmt->execute([(int)$data['id']]);
        }
        return true;
    } catch (Exception $e) {
        error_log("mirror_resume_to_cloud error: " . $e->getMessage());
        return false;
    }
}

/**
 * Mirror Contact Form Message to Supabase Cloud
 */
function mirror_message_to_cloud(array $data): bool {
    if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
        return true;
    }

    $cloud = get_cloud_pdo();
    if (!$cloud) {
        return false;
    }

    try {
        $stmt = $cloud->prepare("
            INSERT INTO messages (name, email, subject, message, ip_address, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['subject'],
            $data['message'],
            $data['ip_address'] ?? null
        ]);
        return true;
    } catch (Exception $e) {
        error_log("mirror_message_to_cloud error: " . $e->getMessage());
        return false;
    }
}

/**
 * Compare local and cloud record counts
 */
function get_sync_difference(PDO $localPdo): ?array {
    if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
        return null;
    }

    $cloud = get_cloud_pdo();
    if (!$cloud) {
        return null;
    }

    try {
        $diff = [];
        foreach (['projects', 'resume', 'messages'] as $table) {
            $localCount = (int)$localPdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            $cloudCount = (int)$cloud->query("SELECT COUNT(*) FROM \"{$table}\"")->fetchColumn();
            if ($localCount !== $cloudCount) {
                $diff[$table] = [
                    'local' => $localCount,
                    'cloud' => $cloudCount
                ];
            }
        }
        return $diff;
    } catch (Exception $e) {
        return null;
    }
}
