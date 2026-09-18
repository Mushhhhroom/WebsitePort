<?php
/**
 * Secure Contact Page (contact.php)
 * Portfolio Project - Jairus John Valdez
 * Implements CSRF tokens, anti-spam honeypot, input sanitization & validation
 */

$page_title = 'Contact & Inquiries | Jairus John Valdez';
require_once __DIR__ . '/includes/header.php';

$feedback = null;
$form_data = [
    'name'    => '',
    'email'   => '',
    'subject' => '',
    'message' => ''
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // 1. Verify CSRF Token
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submitted_token)) {
        $feedback = [
            'type'    => 'error',
            'message' => 'Security token mismatch or expired session. Please refresh and try again.'
        ];
    } 
    // 2. Check Spam Honeypot (must be empty)
    elseif (!empty($_POST['website'])) {
        // Silently discard spam
        $feedback = [
            'type'    => 'success',
            'message' => 'Thank you! Your message has been sent successfully.'
        ];
    } 
    else {
        // 3. Collect and sanitize input
        $name    = sanitize_text($_POST['name'] ?? '');
        $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $subject = sanitize_text($_POST['subject'] ?? 'Portfolio Contact Request');
        $message = sanitize_text($_POST['message'] ?? '');

        $form_data = compact('name', 'email', 'subject', 'message');

        // 4. Validate
        if (empty($name) || strlen($name) < 2) {
            $feedback = ['type' => 'error', 'message' => 'Please provide your name (minimum 2 characters).'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $feedback = ['type' => 'error', 'message' => 'Please enter a valid email address.'];
        } elseif (empty($message) || strlen($message) < 10) {
            $feedback = ['type' => 'error', 'message' => 'Your message is too short. Please provide at least 10 characters.'];
        } else {
            // 5. Store message securely using PDO Prepared Statement
            try {
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                $stmt = $pdo->prepare("
                    INSERT INTO messages (name, email, subject, message, ip_address, is_read, created_at)
                    VALUES (?, ?, ?, ?, ?, 0, NOW())
                ");
                $stmt->execute([$name, $email, $subject, $message, $ip]);

                $feedback = [
                    'type'    => 'success',
                    'message' => 'Thank you, ' . e($name) . '! Your message has been securely recorded. I will get back to you shortly.'
                ];

                // Clear form upon success
                $form_data = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

                // Refresh CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (PDOException $e) {
                error_log("Contact form error: " . $e->getMessage());
                $feedback = [
                    'type'    => 'error',
                    'message' => 'An unexpected database error occurred while sending your message. Please try again later.'
                ];
            }
        }
    }
}
?>

<div class="section">
    <div class="container">
        <div class="section-header" style="text-align: center; max-width: 650px; margin: 0 auto 40px auto;">
            <div class="section-subtitle">// GET IN TOUCH</div>
            <h1 class="section-title">Send a Message</h1>
            <p class="section-desc" style="margin: 0 auto;">
                Have a question about a project, an internship opportunity, or want to discuss systems architecture? Feel free to reach out.
            </p>
        </div>

        <div class="form-card">
            <?php if ($feedback): ?>
                <div class="alert alert-<?php echo e($feedback['type']); ?>">
                    <span><?php echo $feedback['type'] === 'success' ? '✓' : '⚠'; ?></span>
                    <span><?php echo e($feedback['message']); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="contact.php" novalidate>
                <!-- CSRF Token (Crucial for OWASP compliance) -->
                <?php echo csrf_field(); ?>

                <!-- Anti-Bot Honeypot Field (Hidden from real users) -->
                <div style="display: none;" aria-hidden="true">
                    <label for="website">Website (Leave blank):</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">Full Name <span style="color: var(--accent-red);">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control" 
                           placeholder="e.g. Jane Doe" 
                           value="<?php echo e($form_data['name']); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address <span style="color: var(--accent-red);">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="e.g. jane@example.com" 
                           value="<?php echo e($form_data['email']); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           class="form-control" 
                           placeholder="e.g. Project Inquiry / Collaboration" 
                           value="<?php echo e($form_data['subject']); ?>">
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">Message <span style="color: var(--accent-red);">*</span></label>
                    <textarea id="message" 
                              name="message" 
                              class="form-control" 
                              placeholder="Write your message here..." 
                              rows="5" 
                              required><?php echo e($form_data['message']); ?></textarea>
                    <div class="form-help">Protected with prepared statements &amp; CSRF validation.</div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <span>🔒 Send Message Securely</span>
                </button>
            </form>
        </div>

        <div style="margin-top: 50px; text-align: center; color: var(--text-secondary); line-height: 1.8;">
            <p><strong>Email:</strong> <a href="mailto:Valdez.jairusjohn.deleste@gmail.com" style="font-weight: 600; color: var(--accent-cyan);">Valdez.jairusjohn.deleste@gmail.com</a></p>
            <p><strong>Phone:</strong> <a href="tel:+639497844287" style="color: var(--text-primary);">+63 949 784 4287</a> &bull; <strong>Location:</strong> Blk. 2 Lot 1 Kasihoa Llano Road, Caloocan City</p>
            <p style="margin-top: 10px;">
                <a href="https://github.com/Mushhhhroom" target="_blank" rel="noopener noreferrer" style="margin: 0 10px; color: var(--accent-cyan);">GitHub Profile</a> | 
                <a href="https://www.linkedin.com/in/jairus-valdez-19469a313/" target="_blank" rel="noopener noreferrer" style="margin: 0 10px; color: var(--accent-cyan);">LinkedIn Profile</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
