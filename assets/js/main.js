/**
 * Enhanced Portfolio Client-Side Logic & Mobile Interactivity
 * Jairus John Valdez - Computer Science Portfolio
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Drawer Navigation & Backdrop Controls
    const mobileToggle = document.getElementById('mobileToggle') || document.querySelector('.mobile-toggle');
    const navContainer = document.getElementById('navContainer') || document.querySelector('.nav-container');
    const navBackdrop = document.getElementById('navBackdrop') || document.querySelector('.nav-backdrop');
    const navLinks = document.querySelectorAll('.nav-link, .nav-btn-admin');
    const siteHeader = document.querySelector('.site-header');

    function openMobileMenu() {
        if (!mobileToggle || !navContainer) return;
        mobileToggle.classList.add('is-active');
        mobileToggle.setAttribute('aria-expanded', 'true');
        navContainer.classList.add('is-open');
        if (navBackdrop) navBackdrop.classList.add('is-visible');
        document.body.classList.add('menu-open');
    }

    function closeMobileMenu() {
        if (!mobileToggle || !navContainer) return;
        mobileToggle.classList.remove('is-active');
        mobileToggle.setAttribute('aria-expanded', 'false');
        navContainer.classList.remove('is-open');
        if (navBackdrop) navBackdrop.classList.remove('is-visible');
        document.body.classList.remove('menu-open');
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = mobileToggle.classList.contains('is-active');
            if (isOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
    }

    if (navBackdrop) {
        navBackdrop.addEventListener('click', closeMobileMenu);
    }

    // Close mobile menu when clicking any nav link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                closeMobileMenu();
            }
        });
    });

    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    });

    // 2. Header Scroll Effect (compact glass on scroll)
    if (siteHeader) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                siteHeader.classList.add('is-scrolled');
            } else {
                siteHeader.classList.remove('is-scrolled');
            }
        }, { passive: true });
    }

    // 3. Project Filtering & Instant Search (for projects.php)
    const filterButtons = document.querySelectorAll('.filter-btn');
    const searchInput = document.querySelector('#projectSearch');
    const projectCards = document.querySelectorAll('.project-card');

    function filterProjects() {
        const activeBtn = document.querySelector('.filter-btn.active');
        const selectedCategory = activeBtn ? activeBtn.getAttribute('data-category').toLowerCase() : 'all';
        const searchQuery = searchInput ? searchInput.value.trim().toLowerCase() : '';

        let visibleCount = 0;
        projectCards.forEach(card => {
            const cardCategory = (card.getAttribute('data-category') || '').toLowerCase();
            const cardTitle = (card.querySelector('.project-title')?.textContent || '').toLowerCase();
            const cardDesc = (card.querySelector('.project-desc')?.textContent || '').toLowerCase();
            const cardTech = (card.querySelector('.project-tech')?.textContent || '').toLowerCase();

            const matchesCategory = (selectedCategory === 'all' || cardCategory === selectedCategory);
            const matchesSearch = !searchQuery || 
                                  cardTitle.includes(searchQuery) || 
                                  cardDesc.includes(searchQuery) || 
                                  cardTech.includes(searchQuery);

            if (matchesCategory && matchesSearch) {
                card.style.display = 'flex';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                visibleCount++;
            } else {
                card.style.display = 'none';
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
            }
        });

        // Show empty state if no projects match
        const emptyNotice = document.querySelector('#noProjectsNotice');
        if (emptyNotice) {
            emptyNotice.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (filterButtons.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterProjects();
            });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterProjects);
    }

    // 4. Tab Switcher for Dashboard
    const tabButtons = document.querySelectorAll('.dashboard-tabs .tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    if (tabButtons.length > 0 && tabPanels.length > 0) {
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-tab');

                tabButtons.forEach(b => b.classList.remove('active'));
                tabPanels.forEach(p => p.style.display = 'none');

                button.classList.add('active');
                const activePanel = document.querySelector(`#tab-${targetTab}`);
                if (activePanel) {
                    activePanel.style.display = 'block';
                }

                // Update URL hash without scrolling
                history.replaceState(null, null, `#${targetTab}`);
            });
        });

        // Check initial hash in URL
        const currentHash = window.location.hash.replace('#', '');
        if (currentHash) {
            const matchedBtn = document.querySelector(`.dashboard-tabs .tab-btn[data-tab="${currentHash}"]`);
            if (matchedBtn) {
                matchedBtn.click();
            }
        }
    }

    // 5. Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-6px)';
                setTimeout(() => alert.remove(), 500);
            });
        }, 6000);
    }
});
