<?php
/**
 * Global Header Component
 * Portfolio Project - Jairus John Valdez
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

$page_title = $page_title ?? 'Jairus John Valdez | Computer Science Portfolio';

// Normalize current page identifier across environments (Vercel serverless, Apache, clean URLs)
if (!isset($page_id) || empty($page_id)) {
    $uri_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $raw_base = basename(trim($uri_path, '/'));
    $base_clean = preg_replace('/\.php$/i', '', $raw_base);

    if (empty($base_clean) || $base_clean === 'portfolio' || $base_clean === 'index') {
        $page_id = 'home';
    } else {
        $page_id = strtolower($base_clean);
    }
}

$current_script = ($page_id === 'home') ? 'index.php' : ($page_id . '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?></title>
    <meta name="description" content="Official portfolio of Jairus John Valdez — Computer Science Student, Systems Developer, and Security Enthusiast.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Space+Grotesk:wght@500;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Skip to Main Content Link (WCAG 2.4.1) -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">
        <div class="container navbar">
            <a href="index.php" class="brand-monogram" aria-label="Jairus Valdez - Home">
                <span class="status-pip" aria-hidden="true"></span>
                <span>VALDEZ.SYS</span>
                <span class="version-tag">v2.6.4</span>
            </a>

            <button class="mobile-toggle" 
                    id="mobileToggle" 
                    type="button"
                    aria-label="Open navigation menu" 
                    aria-expanded="false" 
                    aria-controls="navContainer">
                <span class="hamburger-box" aria-hidden="true">
                    <span class="hamburger-inner"></span>
                </span>
            </button>

            <nav class="nav-container" id="navContainer">
                <ul class="nav-links" id="navLinks">
                    <li>
                        <a href="index.php" class="nav-link <?php echo $page_id === 'home' ? 'active' : ''; ?>" <?php echo $page_id === 'home' ? 'aria-current="page"' : ''; ?>>
                            <span class="nav-link-icon">🏠</span>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="about.php" class="nav-link <?php echo $page_id === 'about' ? 'active' : ''; ?>" <?php echo $page_id === 'about' ? 'aria-current="page"' : ''; ?>>
                            <span class="nav-link-icon">📄</span>
                            <span>About &amp; Resume</span>
                        </a>
                    </li>
                    <li>
                        <a href="projects.php" class="nav-link <?php echo $page_id === 'projects' ? 'active' : ''; ?>" <?php echo $page_id === 'projects' ? 'aria-current="page"' : ''; ?>>
                            <span class="nav-link-icon">💻</span>
                            <span>Projects</span>
                        </a>
                    </li>
                    <li>
                        <a href="contact.php" class="nav-link <?php echo $page_id === 'contact' ? 'active' : ''; ?>" <?php echo $page_id === 'contact' ? 'aria-current="page"' : ''; ?>>
                            <span class="nav-link-icon">✉️</span>
                            <span>Contact</span>
                        </a>
                    </li>
                    <li>
                        <button type="button" class="nav-btn-telemetry js-system-modal-trigger" aria-haspopup="dialog" aria-label="Open System Architecture and Telemetry Console">
                            <span>⚡ Telemetry</span>
                        </button>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-divider"></li>
                        <li>
                            <a href="dashboard.php" class="nav-btn-admin <?php echo $page_id === 'dashboard' ? 'active' : ''; ?>" <?php echo $page_id === 'dashboard' ? 'aria-current="page"' : ''; ?>>
                                <span>⚙ Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="nav-link nav-link-logout" title="Log Out">
                                <span>Logout 🚪</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-divider"></li>
                        <li>
                            <a href="login.php" class="nav-btn-admin <?php echo $page_id === 'login' ? 'active' : ''; ?>" <?php echo $page_id === 'login' ? 'aria-current="page"' : ''; ?>>
                                <span>🔒 Admin Portal</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <div class="nav-backdrop" id="navBackdrop"></div>
    </header>
    <main id="main-content" tabindex="-1">
