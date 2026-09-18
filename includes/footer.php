<?php
/**
 * Global Footer Component
 * Portfolio Project - Jairus John Valdez
 */
?>
    </main>

    <footer class="site-footer" role="contentinfo">
        <div class="container footer-content">
            <div>
                <p>&copy; <?php echo date('Y'); ?> <strong>Jairus John D. Valdez</strong>. All rights reserved.</p>
                <p style="font-size: 0.85rem; margin-top: 4px; color: var(--text-secondary);">
                    Fourth-Year BS Computer Science Student &bull; Quezon City University
                </p>
            </div>
            <nav aria-label="Footer Navigation">
                <ul class="footer-links">
                    <li><a href="https://github.com/Mushhhhroom" target="_blank" rel="noopener noreferrer" aria-label="GitHub Profile (opens in new window)">GitHub</a></li>
                    <li><a href="https://www.linkedin.com/in/jairus-valdez-19469a313/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn Profile (opens in new window)">LinkedIn</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms</a></li>
                    <li><a href="cookies.php">Cookies</a></li>
                    <li><button type="button" class="footer-btn-link js-cookie-settings-trigger" id="openCookieSettingsBtn" aria-haspopup="dialog">Cookie Preferences</button></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="login.php">Admin Portal</a></li>
                </ul>
            </nav>
        </div>
    </footer>

    <!-- Cookie Consent Banner & Preferences Modal Component -->
    <?php require_once __DIR__ . '/cookie_banner.php'; ?>

    <!-- Mobile Floating Bottom App Dock (Visible on Mobile Screens Only) -->
    <nav class="mobile-dock" aria-label="Quick Mobile Navigation">
        <a href="index.php" class="dock-item <?php echo $current_script === 'index.php' ? 'active' : ''; ?>">
            <span class="dock-icon" aria-hidden="true">🏠</span>
            <span class="dock-label">Home</span>
        </a>
        <a href="about.php" class="dock-item <?php echo $current_script === 'about.php' ? 'active' : ''; ?>">
            <span class="dock-icon" aria-hidden="true">📄</span>
            <span class="dock-label">Resume</span>
        </a>
        <a href="projects.php" class="dock-item <?php echo $current_script === 'projects.php' ? 'active' : ''; ?>">
            <span class="dock-icon" aria-hidden="true">💻</span>
            <span class="dock-label">Projects</span>
        </a>
        <a href="contact.php" class="dock-item <?php echo $current_script === 'contact.php' ? 'active' : ''; ?>">
            <span class="dock-icon" aria-hidden="true">✉️</span>
            <span class="dock-label">Contact</span>
        </a>
        <?php if (is_logged_in()): ?>
            <a href="dashboard.php" class="dock-item <?php echo $current_script === 'dashboard.php' ? 'active' : ''; ?>">
                <span class="dock-icon" aria-hidden="true">⚙️</span>
                <span class="dock-label">Admin</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="dock-item <?php echo $current_script === 'login.php' ? 'active' : ''; ?>">
                <span class="dock-icon" aria-hidden="true">🔒</span>
                <span class="dock-label">Admin</span>
            </a>
        <?php endif; ?>
    </nav>

    <script src="assets/js/cookie-consent.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
