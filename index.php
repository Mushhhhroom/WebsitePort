<?php
/**
 * Homepage (index.php)
 * Portfolio Project - Jairus John Valdez
 */

$page_title = 'Jairus John Valdez | Computer Science & Systems Developer';
require_once __DIR__ . '/includes/header.php';

// Fetch profile data
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
$user_stmt->execute();
$profile = $user_stmt->fetch() ?: [
    'full_name' => 'Jairus John Valdez',
    'headline'  => 'Computer Science Student & Systems Developer',
    'bio'       => 'Computer Science student passionate about system development, cybersecurity, database engineering, and modern full-stack web applications.'
];

// Fetch featured projects
$projects_stmt = $pdo->prepare("SELECT * FROM projects WHERE featured = 1 ORDER BY id DESC LIMIT 3");
$projects_stmt->execute();
$featured_projects = $projects_stmt->fetchAll();

// Fetch counts for statistics
$count_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$count_resume   = $pdo->query("SELECT COUNT(*) FROM resume")->fetchColumn();
$count_messages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
?>

<div class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-kicker">
                <span class="pulse-dot"></span>
                <span>Open for Technical Roles &amp; Collaborations</span>
            </div>
            
            <h1 class="hero-title">
                Hi, I'm <span class="gradient-text"><?php echo e($profile['full_name']); ?></span>.
            </h1>
            
            <p class="hero-description">
                Highly motivated <strong>Fourth-Year Bachelor of Science in Computer Science student</strong> at Quezon City University. Strong technical foundation in system troubleshooting, digital platforms, and software engineering across website, application, and mobile development.
            </p>

            <div class="hero-actions">
                <a href="projects.php" class="btn btn-primary">
                    <span>View Projects</span> &rarr;
                </a>
                <a href="about.php" class="btn btn-outline">
                    <span>Resume &amp; Experience</span>
                </a>
                <a href="contact.php" class="btn btn-outline">
                    <span>Get in Touch</span>
                </a>
            </div>

            <div class="hero-stats">
                <div class="stat-card">
                    <div class="stat-num">4th Year</div>
                    <div class="stat-label">BS Computer Science (QCU)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num">Full-Stack</div>
                    <div class="stat-label">Web, Mobile &amp; Desktop</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num">JPCS</div>
                    <div class="stat-label">Board of Programmer Officer</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num">Multi-DB</div>
                    <div class="stat-label">MySQL, MongoDB &amp; PG/Supabase</div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div class="section-subtitle">// WHAT I BUILD</div>
            <h2 class="section-title">Featured Projects</h2>
            <p class="section-desc">
                A selection of systems, web portals, and database projects crafted with an emphasis on performance and security.
            </p>
        </div>

        <div class="projects-grid">
            <?php if (!empty($featured_projects)): ?>
                <?php foreach ($featured_projects as $proj): ?>
                    <article class="project-card" data-category="<?php echo e($proj['category']); ?>">
                        <div class="project-meta">
                            <span class="project-category"><?php echo e($proj['category']); ?></span>
                            <?php if ($proj['featured']): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                        </div>

                        <h3 class="project-title"><?php echo e($proj['title']); ?></h3>
                        <p class="project-desc"><?php echo e($proj['description']); ?></p>

                        <div class="project-tech">
                            <?php 
                            $tags = array_map('trim', explode(',', $proj['tech_stack']));
                            foreach ($tags as $tag): 
                            ?>
                                <span class="tag-badge"><?php echo e($tag); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="project-links">
                            <?php if (!empty($proj['github_link'])): ?>
                                <a href="<?php echo e($proj['github_link']); ?>" target="_blank" rel="noopener noreferrer" class="link-btn">
                                    <span>&lt;/&gt; GitHub</span>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($proj['demo_link'])): ?>
                                <a href="<?php echo e($proj['demo_link']); ?>" target="_blank" rel="noopener noreferrer" class="link-btn" style="color: var(--accent-emerald);">
                                    <span>↗ Live Demo</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--text-muted);">No featured projects yet. Check back soon!</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <a href="projects.php" class="btn btn-outline">View All <?php echo (int)$count_projects; ?> Projects &rarr;</a>
        </div>
    </div>
</section>

<section class="section" style="background: rgba(17, 24, 39, 0.4); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div class="section-header">
            <div class="section-subtitle">// CORE COMPETENCIES</div>
            <h2 class="section-title">Technical Skills &amp; Focus</h2>
            <p class="section-desc">Practical systems development combined with foundational computer science theory.</p>
        </div>

        <div class="skills-grid">
            <div class="skill-card">
                <h3 class="skill-category" style="color: var(--accent-emerald);">Frontend &amp; UX</h3>
                <p class="skill-list">HTML5, Modern CSS3, JavaScript (ES6+), React/Next.js basics, Responsive Layouts, Tailwind CSS, Accessibility.</p>
            </div>
            <div class="skill-card">
                <h3 class="skill-category" style="color: var(--accent-cyan);">Backend Engineering</h3>
                <p class="skill-list">PHP 8.x, REST APIs, Node.js, Express, Session Architectures, Object-Oriented Programming, MVC Patterns.</p>
            </div>
            <div class="skill-card">
                <h3 class="skill-category" style="color: var(--accent-blue);">Databases &amp; Systems</h3>
                <p class="skill-list">MySQL, PostgreSQL, Prisma ORM, Query Optimization, Normalization, Relational Schemas, Indexing Strategies.</p>
            </div>
            <div class="skill-card">
                <h3 class="skill-category" style="color: var(--accent-purple);">Defensive Security</h3>
                <p class="skill-list">OWASP Top 10 Mitigation, PDO Prepared Statements, CSRF Tokens, Secure Password Hashing (Bcrypt), XSS Defense.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="text-align: center; max-width: 700px;">
        <div class="section-subtitle">// LET'S CONNECT</div>
        <h2 class="section-title" style="margin-bottom: 16px;">Have a project or opportunity?</h2>
        <p class="section-desc" style="margin: 0 auto 30px auto;">
            I am always eager to learn, contribute to challenging software projects, and collaborate with teams building impactful technology.
        </p>
        <a href="contact.php" class="btn btn-primary">Send Me a Message</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
