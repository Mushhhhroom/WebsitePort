<?php
/**
 * Global Header Component
 * Portfolio Project - Jairus John Valdez
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

$page_title = $page_title ?? 'Jairus John Valdez | Computer Science Portfolio';
$current_script = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo e($page_title); ?></title>
    <meta name="description" content="Official portfolio of Jairus John Valdez — Computer Science Student, Systems Developer, and Security Enthusiast.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container navbar">
            <a href="index.php" class="logo">
                <span class="logo-badge">&lt;JV /&gt;</span>
                <span>Jairus Valdez</span>
            </a>

            <button class="mobile-toggle" aria-label="Toggle navigation" aria-expanded="false">☰</button>

            <nav>
                <ul class="nav-links">
                    <li>
                        <a href="index.php" class="nav-link <?php echo $current_script === 'index.php' ? 'active' : ''; ?>">Home</a>
                    </li>
                    <li>
                        <a href="about.php" class="nav-link <?php echo $current_script === 'about.php' ? 'active' : ''; ?>">About & Resume</a>
                    </li>
                    <li>
                        <a href="projects.php" class="nav-link <?php echo $current_script === 'projects.php' ? 'active' : ''; ?>">Projects</a>
                    </li>
                    <li>
                        <a href="contact.php" class="nav-link <?php echo $current_script === 'contact.php' ? 'active' : ''; ?>">Contact</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li>
                            <a href="dashboard.php" class="nav-btn-admin <?php echo $current_script === 'dashboard.php' ? 'active' : ''; ?>">
                                ⚙ Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="nav-link" style="color: #f87171;" title="Log Out">Logout</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="login.php" class="nav-btn-admin <?php echo $current_script === 'login.php' ? 'active' : ''; ?>">
                                🔒 Admin
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
