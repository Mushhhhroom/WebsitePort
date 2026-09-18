<?php
/**
 * Administrator Login Page (login.php)
 * Portfolio Project - Jairus John Valdez
 * Implements CSRF verification, password_verify(), session fixation mitigation, and rate limiting
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error_message = null;
$notice = null;

if (isset($_GET['error']) && $_GET['error'] === 'auth_required') {
    $notice = 'You must be logged in to access the administrative dashboard.';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // 1. Verify CSRF Token
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submitted_token)) {
        $error_message = 'Security session expired. Please refresh the page and try again.';
    } else {
        // 2. Simple Brute-Force Rate Limiting via Session
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0);
        $_SESSION['last_attempt_time'] = ($_SESSION['last_attempt_time'] ?? time());

        // Check if locked out (5 attempts within 5 minutes)
        if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt_time']) < 300) {
            $remaining = 300 - (time() - $_SESSION['last_attempt_time']);
            $error_message = "Too many failed login attempts. Please wait {$remaining} seconds before trying again.";
        } else {
            // Reset attempts if lockout window passed
            if ((time() - $_SESSION['last_attempt_time']) >= 300) {
                $_SESSION['login_attempts'] = 0;
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error_message = 'Please provide both username and password.';
            } else {
                // 3. Query user via Prepared Statement
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                // 4. Verify password with bcrypt
                if ($user && password_verify($password, $user['password'])) {
                    // Prevent session fixation attacks
                    session_regenerate_id(true);

                    // Set authenticated session data
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['username']   = $user['username'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name']  = $user['full_name'];
                    $_SESSION['logged_at']  = time();

                    // Reset failed attempt counters
                    unset($_SESSION['login_attempts'], $_SESSION['last_attempt_time']);

                    // Redirect to dashboard cleanly before any output
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    $error_message = 'Invalid credentials. Please check your username and password.';
                }
            }
        }
    }
}

$page_id = 'login';
$current_script = 'login.php';
$page_title = 'Admin Authentication | Jairus John Valdez';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="container">
        <div class="form-card" style="max-width: 440px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <div style="display: inline-flex; justify-content: center; align-items: center; width: 48px; height: 48px; border-radius: 50%; background: rgba(6, 182, 212, 0.15); color: var(--accent-cyan); font-size: 1.5rem; margin-bottom: 12px;">
                    🔒
                </div>
                <h1 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 6px;">Admin Access</h1>
                <p style="color: var(--text-secondary); font-size: 0.85rem;">Authenticate to manage your portfolio content</p>
            </div>

            <?php if ($notice): ?>
                <div class="alert" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fde68a;" role="status">
                    <span aria-hidden="true">⚠</span>
                    <span><?php echo e($notice); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div id="loginError" class="alert alert-error" role="alert" aria-live="assertive">
                    <span aria-hidden="true">✕</span>
                    <span><?php echo e($error_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter username" 
                           autocomplete="username" 
                           required 
                           aria-required="true"
                           <?php echo $error_message ? 'aria-invalid="true" aria-describedby="loginError"' : ''; ?>
                           autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Enter password" 
                           autocomplete="current-password" 
                           required
                           aria-required="true"
                           <?php echo $error_message ? 'aria-invalid="true"' : ''; ?>>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    <span>Log In to Dashboard</span>
                </button>
            </form>

            <div style="margin-top: 24px; padding: 14px; background: var(--bg-surface-elevated); border-radius: var(--radius-sm); border: 1px dashed var(--border-highlight); font-size: 0.8rem; color: var(--text-muted);">
                <strong style="color: var(--text-secondary);">Default Setup Credentials:</strong><br>
                Username: <code>admin</code><br>
                Password: <code>password123</code>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
