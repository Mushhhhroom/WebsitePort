<?php
/**
 * Projects Showcase Page (projects.php)
 * Portfolio Project - Jairus John Valdez
 */

$page_id = 'projects';
$current_script = 'projects.php';
$page_title = 'Projects Showcase | Jairus John Valdez';
require_once __DIR__ . '/includes/header.php';

// Fetch all projects from database
$stmt = $pdo->query("SELECT * FROM projects ORDER BY featured DESC, id DESC");
$projects = $stmt->fetchAll();

// Extract unique categories for filter pills
$categories = [];
foreach ($projects as $p) {
    if (!empty($p['category']) && !in_array($p['category'], $categories)) {
        $categories[] = $p['category'];
    }
}
?>

<div class="section" id="projectsSection" data-section-name="PROJECTS">
    <div class="container">
        <div class="section-header">
            <div class="section-subtitle">PORTFOLIO &amp; LABS</div>
            <h1 class="section-title">Projects &amp; Systems Showcase</h1>
            <p class="section-desc">
                Exploration of web architectures, database design, backend services, and network infrastructure. Built to solve real problems with secure coding practices.
            </p>
        </div>

        <!-- Filter & Search Controls -->
        <div class="filter-bar">
            <div class="filter-pills" role="tablist" aria-label="Filter projects by category">
                <button class="filter-btn active" data-category="all" role="tab" aria-selected="true">All Projects (<?php echo count($projects); ?>)</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-category="<?php echo e($cat); ?>" role="tab" aria-selected="false">
                        <?php echo e($cat); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="search-box">
                <label for="projectSearch" class="visually-hidden">Search projects by keyword or tech stack</label>
                <input type="search" id="projectSearch" name="projectSearch" placeholder="🔍 Search tech or title..." aria-label="Search projects by keyword or tech stack" autocomplete="off">
            </div>
        </div>

        <!-- Dynamic Screen Reader Live Region for Filter Counts -->
        <div id="projectSearchStatus" class="visually-hidden" role="status" aria-live="polite"></div>

        <!-- Projects Grid -->
        <div class="projects-grid" id="projectsContainer" role="region" aria-label="Projects Showcase Grid">
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $proj): ?>
                    <article class="project-card" data-category="<?php echo e($proj['category']); ?>">
                        <div class="project-meta">
                            <span class="project-category"><?php echo e($proj['category']); ?></span>
                            <?php if (!empty($proj['featured'])): ?>
                                <span class="featured-badge">★ Featured</span>
                            <?php endif; ?>
                        </div>

                        <h2 class="project-title"><?php echo e($proj['title']); ?></h2>
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
                <p style="color: var(--text-muted); grid-column: 1 / -1;">No projects currently available in the database.</p>
            <?php endif; ?>
        </div>

        <div id="noProjectsNotice" style="display: none; text-align: center; padding: 40px; color: var(--text-muted);" role="status" aria-live="polite">
            <p style="font-size: 1.1rem;">No matching projects found for your filter criteria.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
