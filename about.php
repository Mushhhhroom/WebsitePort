<?php
/**
 * Dynamic Resume & About Page (about.php)
 * Portfolio Project - Jairus John Valdez
 */

$page_title = 'About & Resume | Jairus John Valdez';
require_once __DIR__ . '/includes/header.php';

// Fetch user profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
$stmt->execute();
$profile = $stmt->fetch();

// Fetch all resume entries grouped by section
$resume_stmt = $pdo->query("SELECT * FROM resume ORDER BY section ASC, display_order ASC, id ASC");
$all_resume = $resume_stmt->fetchAll();

$sections = [
    'education'      => [],
    'experience'     => [],
    'skills'         => [],
    'certifications' => []
];

foreach ($all_resume as $item) {
    $sec = strtolower($item['section']);
    if (!isset($sections[$sec])) {
        $sections[$sec] = [];
    }
    $sections[$sec][] = $item;
}
?>

<div class="section">
    <div class="container">
        <!-- Profile Banner -->
        <div class="profile-banner">
            <div class="banner-layout">
                <div class="banner-info">
                    <span class="tag-badge tag-badge-green" style="margin-bottom: 12px;">Fourth-Year BS Computer Science &bull; QCU</span>
                    <h1 class="banner-name">
                        <?php echo e($profile['full_name'] ?? 'Jairus John D. Valdez'); ?>
                    </h1>
                    <p class="banner-headline">
                        <?php echo e($profile['headline'] ?? 'Fourth-Year BS Computer Science Student | Website, Application & Mobile Developer'); ?>
                    </p>
                    <p class="banner-bio">
                        <?php echo e($profile['bio'] ?? ''); ?>
                    </p>
                    <div class="contact-chips">
                        <span class="chip-item">📍 Caloocan City, PH</span>
                        <a href="tel:+639497844287" class="chip-item chip-link">📞 +63 949 784 4287</a>
                        <a href="mailto:Valdez.jairusjohn.deleste@gmail.com" class="chip-item chip-link">✉️ Valdez.jairusjohn.deleste@gmail.com</a>
                        <a href="https://github.com/Mushhhhroom" target="_blank" rel="noopener noreferrer" class="chip-item chip-link">💻 GitHub</a>
                        <a href="https://www.linkedin.com/in/jairus-valdez-19469a313/" target="_blank" rel="noopener noreferrer" class="chip-item chip-link">💼 LinkedIn</a>
                    </div>
                </div>
                <div class="banner-actions">
                    <a href="contact.php" class="btn btn-primary btn-banner">Contact Me</a>
                    <button onclick="window.print()" class="btn btn-outline btn-banner">
                        🖨️ Print / Save PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Section: Education -->
        <section class="resume-section">
            <h2 class="resume-section-title">
                <span class="icon">🎓</span>
                <span>Education</span>
            </h2>

            <div class="timeline">
                <?php if (!empty($sections['education'])): ?>
                    <?php foreach ($sections['education'] as $edu): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-card">
                                <div class="timeline-header">
                                    <h3 class="timeline-title"><?php echo e($edu['title']); ?></h3>
                                    <?php if (!empty($edu['date_range'])): ?>
                                        <span class="timeline-date"><?php echo e($edu['date_range']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($edu['subtitle'])): ?>
                                    <div class="timeline-subtitle"><?php echo e($edu['subtitle']); ?></div>
                                <?php endif; ?>
                                <p class="timeline-content"><?php echo nl2br(e($edu['content'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No education records listed.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section: Technical Experience & Academic Projects -->
        <section class="resume-section">
            <h2 class="resume-section-title">
                <span class="icon">💼</span>
                <span>Experience &amp; Leadership</span>
            </h2>

            <div class="timeline">
                <?php if (!empty($sections['experience'])): ?>
                    <?php foreach ($sections['experience'] as $exp): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot" style="border-color: var(--accent-emerald);"></div>
                            <div class="timeline-card">
                                <div class="timeline-header">
                                    <h3 class="timeline-title"><?php echo e($exp['title']); ?></h3>
                                    <?php if (!empty($exp['date_range'])): ?>
                                        <span class="timeline-date"><?php echo e($exp['date_range']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($exp['subtitle'])): ?>
                                    <div class="timeline-subtitle" style="color: var(--accent-emerald);"><?php echo e($exp['subtitle']); ?></div>
                                <?php endif; ?>
                                <p class="timeline-content"><?php echo nl2br(e($exp['content'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No experience entries listed.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section: Technical Skills -->
        <section class="resume-section">
            <h2 class="resume-section-title">
                <span class="icon">⚡</span>
                <span>Technical Skills Inventory</span>
            </h2>

            <div class="skills-grid">
                <?php if (!empty($sections['skills'])): ?>
                    <?php foreach ($sections['skills'] as $skill): ?>
                        <div class="skill-card">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                                <h3 class="skill-category"><?php echo e($skill['title']); ?></h3>
                                <?php if (!empty($skill['date_range'])): ?>
                                    <span class="tag-badge"><?php echo e($skill['date_range']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($skill['subtitle'])): ?>
                                <p style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 8px;"><?php echo e($skill['subtitle']); ?></p>
                            <?php endif; ?>
                            <p class="skill-list"><?php echo nl2br(e($skill['content'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No skills listed.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section: Certifications & Coursework -->
        <section class="resume-section">
            <h2 class="resume-section-title">
                <span class="icon">📜</span>
                <span>Certifications &amp; Training</span>
            </h2>

            <div class="timeline">
                <?php if (!empty($sections['certifications'])): ?>
                    <?php foreach ($sections['certifications'] as $cert): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot" style="border-color: var(--accent-purple);"></div>
                            <div class="timeline-card">
                                <div class="timeline-header">
                                    <h3 class="timeline-title"><?php echo e($cert['title']); ?></h3>
                                    <?php if (!empty($cert['date_range'])): ?>
                                        <span class="timeline-date"><?php echo e($cert['date_range']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($cert['subtitle'])): ?>
                                    <div class="timeline-subtitle" style="color: var(--accent-purple);"><?php echo e($cert['subtitle']); ?></div>
                                <?php endif; ?>
                                <p class="timeline-content"><?php echo nl2br(e($cert['content'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No certifications listed.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
