<?php
/**
 * Administrator Control Panel (dashboard.php)
 * Portfolio Project - Jairus John Valdez
 * Full CRUD for Projects, Resume Entries, Contact Messages, and Account Settings
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/cloud_sync.php';

// Auth Guard (Redirects before any HTML if not logged in)
require_login();

$user = current_user();
$alert = null;

// ==========================================================
// ACTION CONTROLLERS (POST REQUESTS)
// ==========================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submitted_token)) {
        $alert = ['type' => 'error', 'message' => 'CSRF verification failed or session expired.'];
    } else {
        $action = $_POST['action'] ?? '';

        // --------------------------------------------------
        // 1. ADD PROJECT
        // --------------------------------------------------
        if ($action === 'add_project') {
            $title       = sanitize_text($_POST['title'] ?? '');
            $description = sanitize_text($_POST['description'] ?? '');
            $tech_stack  = sanitize_text($_POST['tech_stack'] ?? '');
            $category    = sanitize_text($_POST['category'] ?? 'Web Application');
            $github_link = filter_var(trim($_POST['github_link'] ?? ''), FILTER_SANITIZE_URL);
            $demo_link   = filter_var(trim($_POST['demo_link'] ?? ''), FILTER_SANITIZE_URL);
            $featured    = isset($_POST['featured']) ? 1 : 0;

            if (empty($title) || empty($description) || empty($tech_stack)) {
                $alert = ['type' => 'error', 'message' => 'Please fill in all required project fields (title, description, tech stack).'];
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO projects (title, description, tech_stack, category, github_link, demo_link, featured)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $description, $tech_stack, $category, $github_link, $demo_link, $featured]);
                mirror_project_to_cloud('add', compact('title', 'description', 'tech_stack', 'category', 'github_link', 'demo_link', 'featured'));
                $alert = ['type' => 'success', 'message' => 'New project added successfully and mirrored across databases!'];
            }
        }

        // --------------------------------------------------
        // 2. EDIT PROJECT
        // --------------------------------------------------
        elseif ($action === 'edit_project') {
            $id          = (int)($_POST['project_id'] ?? 0);
            $title       = sanitize_text($_POST['title'] ?? '');
            $description = sanitize_text($_POST['description'] ?? '');
            $tech_stack  = sanitize_text($_POST['tech_stack'] ?? '');
            $category    = sanitize_text($_POST['category'] ?? 'Web Application');
            $github_link = filter_var(trim($_POST['github_link'] ?? ''), FILTER_SANITIZE_URL);
            $demo_link   = filter_var(trim($_POST['demo_link'] ?? ''), FILTER_SANITIZE_URL);
            $featured    = isset($_POST['featured']) ? 1 : 0;

            if ($id <= 0 || empty($title) || empty($description) || empty($tech_stack)) {
                $alert = ['type' => 'error', 'message' => 'Invalid project data submitted.'];
            } else {
                $stmt = $pdo->prepare("
                    UPDATE projects 
                    SET title = ?, description = ?, tech_stack = ?, category = ?, github_link = ?, demo_link = ?, featured = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $description, $tech_stack, $category, $github_link, $demo_link, $featured, $id]);
                mirror_project_to_cloud('edit', compact('id', 'title', 'description', 'tech_stack', 'category', 'github_link', 'demo_link', 'featured'));
                $alert = ['type' => 'success', 'message' => 'Project updated successfully and mirrored across databases!'];
            }
        }

        // --------------------------------------------------
        // 3. DELETE PROJECT
        // --------------------------------------------------
        elseif ($action === 'delete_project') {
            $id = (int)($_POST['project_id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                mirror_project_to_cloud('delete', compact('id'));
                $alert = ['type' => 'success', 'message' => 'Project removed successfully and mirrored across databases!'];
            }
        }

        // --------------------------------------------------
        // 4. ADD RESUME ITEM
        // --------------------------------------------------
        elseif ($action === 'add_resume_item') {
            $section       = sanitize_text($_POST['section'] ?? 'experience');
            $title         = sanitize_text($_POST['title'] ?? '');
            $subtitle      = sanitize_text($_POST['subtitle'] ?? '');
            $date_range    = sanitize_text($_POST['date_range'] ?? '');
            $content       = sanitize_text($_POST['content'] ?? '');
            $display_order = (int)($_POST['display_order'] ?? 0);

            if (empty($title) || empty($content)) {
                $alert = ['type' => 'error', 'message' => 'Resume item title and content are required.'];
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO resume (section, title, subtitle, date_range, content, display_order)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$section, $title, $subtitle, $date_range, $content, $display_order]);
                mirror_resume_to_cloud('add', compact('section', 'title', 'subtitle', 'date_range', 'content', 'display_order'));
                $alert = ['type' => 'success', 'message' => 'Resume entry added successfully and mirrored across databases!'];
            }
        }

        // --------------------------------------------------
        // 5. DELETE RESUME ITEM
        // --------------------------------------------------
        elseif ($action === 'delete_resume_item') {
            $id = (int)($_POST['resume_id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM resume WHERE id = ?");
                $stmt->execute([$id]);
                mirror_resume_to_cloud('delete', compact('id'));
                $alert = ['type' => 'success', 'message' => 'Resume entry deleted successfully and mirrored across databases!'];
            }
        }

        // --------------------------------------------------
        // 6. TOGGLE / DELETE MESSAGE
        // --------------------------------------------------
        elseif ($action === 'toggle_message_read') {
            $id = (int)($_POST['message_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE messages SET is_read = (CASE WHEN is_read = 1 THEN 0 ELSE 1 END) WHERE id = ?");
            $stmt->execute([$id]);
            $alert = ['type' => 'success', 'message' => 'Message status updated.'];
        }
        elseif ($action === 'delete_message') {
            $id = (int)($_POST['message_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$id]);
            $alert = ['type' => 'success', 'message' => 'Message deleted successfully!'];
        }

        // --------------------------------------------------
        // 7. UPDATE PROFILE
        // --------------------------------------------------
        elseif ($action === 'update_profile') {
            $full_name = sanitize_text($_POST['full_name'] ?? '');
            $headline  = sanitize_text($_POST['headline'] ?? '');
            $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $bio       = sanitize_text($_POST['bio'] ?? '');
            $github    = filter_var(trim($_POST['github'] ?? ''), FILTER_SANITIZE_URL);
            $linkedin  = filter_var(trim($_POST['linkedin'] ?? ''), FILTER_SANITIZE_URL);

            if (empty($full_name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $alert = ['type' => 'error', 'message' => 'Valid name and email are required.'];
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = ?, headline = ?, email = ?, bio = ?, github = ?, linkedin = ?
                    WHERE id = ?
                ");
                $stmt->execute([$full_name, $headline, $email, $bio, $github, $linkedin, $user['id']]);
                $_SESSION['user_name']  = $full_name;
                $_SESSION['user_email'] = $email;

                mirror_user_update_to_cloud($user['id'], compact('full_name', 'headline', 'email', 'bio', 'github', 'linkedin'));
                set_auth_cookie([
                    'id'        => $user['id'],
                    'username'  => $user['username'],
                    'email'     => $email,
                    'full_name' => $full_name
                ]);
                $alert = ['type' => 'success', 'message' => 'Profile updated successfully across both Local MySQL and Supabase Cloud!'];
            }
        }

        // --------------------------------------------------
        // 8. CHANGE PASSWORD
        // --------------------------------------------------
        elseif ($action === 'change_password') {
            $current_pw = $_POST['current_password'] ?? '';
            $new_pw     = $_POST['new_password'] ?? '';
            $confirm_pw = $_POST['confirm_password'] ?? '';

            if (strlen($new_pw) < 8) {
                $alert = ['type' => 'error', 'message' => 'New password must be at least 8 characters long.'];
            } elseif ($new_pw !== $confirm_pw) {
                $alert = ['type' => 'error', 'message' => 'New password confirmation does not match.'];
            } else {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $curr_hash = $stmt->fetchColumn();

                if ($curr_hash && password_verify($current_pw, $curr_hash)) {
                    $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
                    $up_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $up_stmt->execute([$new_hash, $user['id']]);

                    mirror_user_update_to_cloud($user['id'], ['password' => $new_hash]);
                    set_auth_cookie([
                        'id'        => $user['id'],
                        'username'  => $user['username'],
                        'email'     => $user['email'] ?? '',
                        'full_name' => $user['name'] ?? 'Admin'
                    ]);
                    $alert = ['type' => 'success', 'message' => 'Password updated securely across both Local MySQL and Supabase Cloud!'];
                } else {
                    $alert = ['type' => 'error', 'message' => 'Current password entered is incorrect.'];
                }
            }
        }
    }
}

// Fetch Latest Data for Displays
$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
$resume_items = $pdo->query("SELECT * FROM resume ORDER BY section ASC, display_order ASC, id DESC")->fetchAll();
$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

$user_data_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_data_stmt->execute([$user['id']]);
$profile = $user_data_stmt->fetch();

$unread_count = 0;
foreach ($messages as $m) {
    if (!$m['is_read']) $unread_count++;
}

$sync_diff = get_sync_difference($pdo);

$page_id = 'dashboard';
$current_script = 'dashboard.php';
$page_title = 'Admin Dashboard | Jairus John Valdez';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section" style="padding-top: 40px;">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div>
                <span class="tag-badge tag-badge-green">Authenticated Console</span>
                <h1 style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
                    Control Center
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    Logged in as <strong><?php echo e($user['username']); ?></strong> (<?php echo e($profile['full_name'] ?? 'Admin'); ?>)
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="sync_db.php" class="btn btn-outline btn-sm" style="border-color: #38bdf8; color: #38bdf8;">🔄 DB Sync</a>
                <a href="index.php" class="btn btn-outline btn-sm">👁️ View Public Site</a>
                <a href="logout.php" class="btn btn-danger btn-sm">🚪 Log Out</a>
            </div>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-<?php echo e($alert['type']); ?>">
                <span><?php echo $alert['type'] === 'success' ? '✓' : '⚠'; ?></span>
                <span><?php echo e($alert['message']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($sync_diff)): ?>
            <div class="alert" style="background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.4); color: #bae6fd; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.25rem;">🔄</span>
                    <div>
                        <strong>Database Notice:</strong> Differences detected between Local MySQL and Supabase Cloud (e.g. updates on Vercel).
                    </div>
                </div>
                <a href="sync_db.php" class="btn btn-primary btn-sm" style="margin: 0; padding: 6px 14px; font-size: 0.85rem;">Review &amp; Sync Now &rarr;</a>
            </div>
        <?php endif; ?>

        <!-- Dashboard Navigation Tabs -->
        <div class="dashboard-tabs">
            <button class="tab-btn active" data-tab="overview">📊 Overview</button>
            <button class="tab-btn" data-tab="projects">💻 Projects (<?php echo count($projects); ?>)</button>
            <button class="tab-btn" data-tab="resume">📄 Resume (<?php echo count($resume_items); ?>)</button>
            <button class="tab-btn" data-tab="messages">
                📬 Messages (<?php echo count($messages); ?>)
                <?php if ($unread_count > 0): ?>
                    <span class="tag-badge" style="background: rgba(239,68,68,0.2); color: #fca5a5; margin-left: 4px;"><?php echo $unread_count; ?> new</span>
                <?php endif; ?>
            </button>
            <button class="tab-btn" data-tab="settings">⚙️ Settings</button>
        </div>

        <!-- ==================================================== -->
        <!-- TAB 1: OVERVIEW -->
        <!-- ==================================================== -->
        <div id="tab-overview" class="tab-panel">
            <div class="hero-stats" style="margin-top: 0; margin-bottom: 40px;">
                <div class="stat-card">
                    <div class="stat-num"><?php echo count($projects); ?></div>
                    <div class="stat-label">Total Projects</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?php echo count($resume_items); ?></div>
                    <div class="stat-label">Resume Records</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num" style="color: var(--accent-emerald);"><?php echo count($messages); ?></div>
                    <div class="stat-label">Messages Received</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num" style="color: <?php echo $unread_count > 0 ? '#f87171' : 'var(--accent-cyan)'; ?>">
                        <?php echo $unread_count; ?>
                    </div>
                    <div class="stat-label">Unread Messages</div>
                </div>
            </div>

            <!-- Quick Access Message Summary -->
            <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px;">
                <h3 style="margin-bottom: 16px;">Recent Contact Messages</h3>
                <?php if (empty($messages)): ?>
                    <p style="color: var(--text-muted);">No messages have been submitted through the contact form yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Sender</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($messages, 0, 5) as $msg): ?>
                                    <tr>
                                        <td>
                                            <?php if ($msg['is_read']): ?>
                                                <span class="tag-badge">Read</span>
                                            <?php else: ?>
                                                <span class="tag-badge tag-badge-green">New</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo e($msg['name']); ?></strong></td>
                                        <td><?php echo e($msg['email']); ?></td>
                                        <td><?php echo e($msg['subject']); ?></td>
                                        <td style="font-family: var(--font-mono); font-size: 0.8rem;"><?php echo e($msg['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- TAB 2: PROJECTS MANAGEMENT -->
        <!-- ==================================================== -->
        <div id="tab-projects" class="tab-panel" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2>Manage Projects</h2>
                <button onclick="document.getElementById('addProjectForm').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary btn-sm">
                    + Add New Project
                </button>
            </div>

            <div class="table-responsive" style="margin-bottom: 40px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Tech Stack</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projects)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted);">No projects created yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($projects as $p): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($p['title']); ?></strong>
                                    </td>
                                    <td><span class="tag-badge"><?php echo e($p['category']); ?></span></td>
                                    <td style="font-size: 0.85rem; max-width: 250px;"><?php echo e($p['tech_stack']); ?></td>
                                    <td><?php echo $p['featured'] ? '⭐ Yes' : 'No'; ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button type="button" 
                                                    class="btn btn-outline btn-sm btn-edit-project" 
                                                    data-id="<?php echo (int)$p['id']; ?>"
                                                    data-title="<?php echo e($p['title']); ?>"
                                                    data-category="<?php echo e($p['category']); ?>"
                                                    data-tech="<?php echo e($p['tech_stack']); ?>"
                                                    data-desc="<?php echo e($p['description']); ?>"
                                                    data-github="<?php echo e($p['github_link'] ?? ''); ?>"
                                                    data-demo="<?php echo e($p['demo_link'] ?? ''); ?>"
                                                    data-featured="<?php echo (int)$p['featured']; ?>"
                                                    style="padding: 5px 12px; font-size: 0.8rem;">
                                                ✏️ Edit
                                            </button>
                                            <form method="POST" action="dashboard.php" onsubmit="return confirm('Are you sure you want to delete this project?');" style="display: inline; margin: 0;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="delete_project">
                                                <input type="hidden" name="project_id" value="<?php echo (int)$p['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 5px 12px; font-size: 0.8rem;">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Project Form Card -->
            <div id="addProjectForm" class="form-card" style="max-width: 800px; margin: 0;">
                <h3 style="margin-bottom: 20px;">Add New Project</h3>
                <form method="POST" action="dashboard.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add_project">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Project Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Distributed Task Scheduler">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control">
                                <option value="Web Application">Web Application</option>
                                <option value="Full Stack">Full Stack</option>
                                <option value="Database System">Database System</option>
                                <option value="Security & Backend">Security &amp; Backend</option>
                                <option value="Networking & Systems">Networking &amp; Systems</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Describe what the project does, algorithms or architectures used..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tech Stack (Comma-separated) *</label>
                        <input type="text" name="tech_stack" class="form-control" required placeholder="e.g. PHP 8, MySQL, Redis, Docker">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">GitHub URL</label>
                            <input type="url" name="github_link" class="form-control" placeholder="https://github.com/...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Live Demo URL</label>
                            <input type="url" name="demo_link" class="form-control" placeholder="https://...">
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="featured" name="featured" value="1" style="width: 18px; height: 18px;">
                        <label for="featured" class="form-label" style="margin-bottom: 0;">Highlight on Homepage as Featured Project</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Project</button>
                </form>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- TAB 3: RESUME MANAGEMENT -->
        <!-- ==================================================== -->
        <div id="tab-resume" class="tab-panel" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2>Manage Dynamic Resume</h2>
                <button onclick="document.getElementById('addResumeForm').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary btn-sm">
                    + Add Resume Entry
                </button>
            </div>

            <div class="table-responsive" style="margin-bottom: 40px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Title / Subtitle</th>
                            <th>Date / Level</th>
                            <th>Content</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resume_items)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted);">No resume entries found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resume_items as $r): ?>
                                <tr>
                                    <td><span class="tag-badge tag-badge-green"><?php echo e(ucfirst($r['section'])); ?></span></td>
                                    <td>
                                        <strong><?php echo e($r['title']); ?></strong>
                                        <?php if ($r['subtitle']): ?>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo e($r['subtitle']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-family: var(--font-mono); font-size: 0.8rem;"><?php echo e($r['date_range']); ?></td>
                                    <td style="font-size: 0.85rem; max-width: 300px;"><?php echo e(substr($r['content'], 0, 90)); ?><?php echo strlen($r['content']) > 90 ? '...' : ''; ?></td>
                                    <td><?php echo (int)$r['display_order']; ?></td>
                                    <td>
                                        <form method="POST" action="dashboard.php" onsubmit="return confirm('Delete this resume item?');" style="display: inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete_resume_item">
                                            <input type="hidden" name="resume_id" value="<?php echo (int)$r['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Resume Item Form -->
            <div id="addResumeForm" class="form-card" style="max-width: 800px; margin: 0;">
                <h3 style="margin-bottom: 20px;">Add Resume Record</h3>
                <form method="POST" action="dashboard.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add_resume_item">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Resume Section *</label>
                            <select name="section" class="form-control" required>
                                <option value="education">Education</option>
                                <option value="experience">Experience</option>
                                <option value="skills">Technical Skills</option>
                                <option value="certifications">Certifications</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="1" min="0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Title / Degree / Skill Category *</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. BS Computer Science">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subtitle / Institution / Role</label>
                            <input type="text" name="subtitle" class="form-control" placeholder="e.g. University Name / Department">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date Range or Proficiency</label>
                        <input type="text" name="date_range" class="form-control" placeholder="e.g. 2022 - Present or Advanced">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Content / Responsibilities / Details *</label>
                        <textarea name="content" class="form-control" rows="4" required placeholder="Detail the achievements, coursework, or items included..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Resume Entry</button>
                </form>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- TAB 4: MESSAGES INBOX -->
        <!-- ==================================================== -->
        <div id="tab-messages" class="tab-panel" style="display: none;">
            <h2 style="margin-bottom: 24px;">Inquiries &amp; Messages</h2>

            <?php if (empty($messages)): ?>
                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; text-align: center; color: var(--text-muted);">
                    No contact messages yet.
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach ($messages as $msg): ?>
                        <div style="background: var(--bg-surface); border: 1px solid <?php echo $msg['is_read'] ? 'var(--border-color)' : 'var(--accent-cyan)'; ?>; border-radius: var(--radius-md); padding: 24px;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                                <div>
                                    <span class="tag-badge <?php echo $msg['is_read'] ? '' : 'tag-badge-green'; ?>">
                                        <?php echo $msg['is_read'] ? 'Read' : 'New Message'; ?>
                                    </span>
                                    <h3 style="display: inline-block; margin-left: 10px; font-size: 1.15rem; color: #ffffff;">
                                        <?php echo e($msg['name']); ?>
                                    </h3>
                                    <span style="color: var(--text-muted); font-size: 0.9rem;"> &lt;<?php echo e($msg['email']); ?>&gt;</span>
                                </div>
                                <div style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo e($msg['created_at']); ?>
                                </div>
                            </div>

                            <div style="margin-bottom: 12px;">
                                <strong style="color: var(--accent-cyan);">Subject:</strong> <?php echo e($msg['subject']); ?>
                            </div>

                            <div style="background: var(--bg-surface-elevated); padding: 16px; border-radius: var(--radius-sm); color: var(--text-secondary); line-height: 1.6; margin-bottom: 16px; white-space: pre-wrap;"><?php echo e($msg['message']); ?></div>

                            <div style="display: flex; gap: 10px; align-items: center;">
                                <a href="mailto:<?php echo e($msg['email']); ?>?subject=Re:%20<?php echo urlencode($msg['subject']); ?>" class="btn btn-primary btn-sm">
                                    ✉️ Reply via Email
                                </a>

                                <form method="POST" action="dashboard.php" style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_message_read">
                                    <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        <?php echo $msg['is_read'] ? 'Mark Unread' : 'Mark as Read'; ?>
                                    </button>
                                </form>

                                <form method="POST" action="dashboard.php" onsubmit="return confirm('Delete this message permanently?');" style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ==================================================== -->
        <!-- TAB 5: SETTINGS (PROFILE & PASSWORD) -->
        <!-- ==================================================== -->
        <div id="tab-settings" class="tab-panel" style="display: none;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 28px;">
                <!-- Profile Settings -->
                <div class="form-card" style="margin: 0; max-width: 100%;">
                    <h3 style="margin-bottom: 20px;">Profile Information</h3>
                    <form method="POST" action="dashboard.php">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo e($profile['full_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Professional Headline *</label>
                            <input type="text" name="headline" class="form-control" value="<?php echo e($profile['headline'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e($profile['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Biography / About Summary</label>
                            <textarea name="bio" class="form-control" rows="3"><?php echo e($profile['bio'] ?? ''); ?></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">GitHub URL</label>
                                <input type="url" name="github" class="form-control" value="<?php echo e($profile['github'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" name="linkedin" class="form-control" value="<?php echo e($profile['linkedin'] ?? ''); ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>

                <!-- Password Change Settings -->
                <div class="form-card" style="margin: 0; max-width: 100%;">
                    <h3 style="margin-bottom: 20px;">Security &amp; Password</h3>
                    <form method="POST" action="dashboard.php">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label class="form-label">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required placeholder="••••••••">
                        </div>

                        <div class="form-group">
                            <label class="form-label">New Password *</label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Minimum 8 characters">
                            <div class="form-help">Hashed with bcrypt (PASSWORD_DEFAULT).</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required placeholder="Re-type new password">
                        </div>

                        <button type="submit" class="btn btn-outline" style="border-color: var(--accent-cyan); color: var(--accent-cyan);">
                            Update Password Securely
                        </button>
                    </form>
                </div>

                <!-- Database Configuration & Sync -->
                <div class="form-card" style="margin: 0; max-width: 100%; grid-column: 1 / -1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                        <h3 style="margin: 0;">Database Engine &amp; Cloud Synchronization</h3>
                        <a href="sync_db.php" class="btn btn-primary btn-sm">Open DB Sync Center &rarr;</a>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px;">
                            <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Active Engine</div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: #38bdf8; margin-top: 4px;">
                                <?php echo DB_TYPE === 'mysql' ? '🐬 Local MySQL' : '🐘 Supabase PostgreSQL'; ?>
                            </div>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px;">
                            <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Host &amp; Port</div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-top: 4px;">
                                <?php echo e(DB_HOST . ':' . DB_PORT); ?>
                            </div>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px;">
                            <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Database Name</div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-top: 4px;">
                                <?php echo e(DB_NAME); ?>
                            </div>
                        </div>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">
                        To switch between Local MySQL and Supabase Cloud, toggle <code>DB_TYPE=mysql</code> or <code>DB_TYPE=pgsql</code> in your <code>.env</code> file. Use the <strong>DB Sync Center</strong> to keep records aligned.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Edit Project Modal Component -->
<div id="editProjectModal" class="cookie-modal-backdrop" style="display: none;">
    <div class="cookie-modal" style="max-width: 720px;" role="dialog" aria-modal="true" aria-labelledby="editProjectModalTitle">
        <div class="cookie-modal-header">
            <div>
                <h2 class="cookie-modal-title" id="editProjectModalTitle">Edit Project</h2>
                <p class="cookie-modal-subtitle">Update project information in the database</p>
            </div>
            <button type="button" class="cookie-modal-close" id="closeEditProjectModalBtn" aria-label="Close dialog">&times;</button>
        </div>
        <form method="POST" action="dashboard.php" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
            <div class="cookie-modal-body" style="max-height: 70vh; overflow-y: auto;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_project">
                <input type="hidden" name="project_id" id="edit_project_id">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label for="edit_project_title" class="form-label">Project Title *</label>
                        <input type="text" name="title" id="edit_project_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_project_category" class="form-label">Category</label>
                        <select name="category" id="edit_project_category" class="form-control">
                            <option value="Web Application">Web Application</option>
                            <option value="Full Stack">Full Stack</option>
                            <option value="Full Stack (Mobile & Desktop)">Full Stack (Mobile &amp; Desktop)</option>
                            <option value="Desktop Application">Desktop Application</option>
                            <option value="Database System">Database System</option>
                            <option value="Security & Backend">Security &amp; Backend</option>
                            <option value="Networking & Systems">Networking &amp; Systems</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_project_desc" class="form-label">Description *</label>
                    <textarea name="description" id="edit_project_desc" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_project_tech" class="form-label">Tech Stack (Comma-separated) *</label>
                    <input type="text" name="tech_stack" id="edit_project_tech" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label for="edit_project_github" class="form-label">GitHub URL</label>
                        <input type="url" name="github_link" id="edit_project_github" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_project_demo" class="form-label">Live Demo URL</label>
                        <input type="url" name="demo_link" id="edit_project_demo" class="form-control">
                    </div>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="edit_project_featured" name="featured" value="1" style="width: 18px; height: 18px;">
                    <label for="edit_project_featured" class="form-label" style="margin-bottom: 0; cursor: pointer;">Highlight on Homepage as Featured Project</label>
                </div>
            </div>
            <div class="cookie-modal-footer">
                <button type="button" class="btn btn-outline btn-sm" id="cancelEditProjectBtn">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">💾 Update Project</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editProjectModal');
    const closeBtn = document.getElementById('closeEditProjectModalBtn');
    const cancelBtn = document.getElementById('cancelEditProjectBtn');

    function openEditModal(btn) {
        if (!editModal) return;
        document.getElementById('edit_project_id').value = btn.getAttribute('data-id') || '';
        document.getElementById('edit_project_title').value = btn.getAttribute('data-title') || '';
        document.getElementById('edit_project_category').value = btn.getAttribute('data-category') || 'Web Application';
        document.getElementById('edit_project_desc').value = btn.getAttribute('data-desc') || '';
        document.getElementById('edit_project_tech').value = btn.getAttribute('data-tech') || '';
        document.getElementById('edit_project_github').value = btn.getAttribute('data-github') || '';
        document.getElementById('edit_project_demo').value = btn.getAttribute('data-demo') || '';
        document.getElementById('edit_project_featured').checked = btn.getAttribute('data-featured') === '1';

        editModal.style.display = 'flex';
        document.body.classList.add('cookie-modal-open');
    }

    function closeEditModal() {
        if (!editModal) return;
        editModal.style.display = 'none';
        document.body.classList.remove('cookie-modal-open');
    }

    document.querySelectorAll('.btn-edit-project').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openEditModal(this);
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeEditModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeEditModal);
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) closeEditModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && editModal && editModal.style.display === 'flex') {
            closeEditModal();
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
