/**
 * Portfolio Client-Side Logic & Interactivity
 * Jairus John Valdez - Computer Science Portfolio
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            const isOpen = navLinks.classList.contains('open');
            mobileToggle.setAttribute('aria-expanded', isOpen);
            mobileToggle.innerHTML = isOpen ? '✕' : '☰';
        });
    }

    // 2. Project Filtering & Instant Search (for projects.php)
    const filterButtons = document.querySelectorAll('.filter-btn');
    const searchInput = document.querySelector('#projectSearch');
    const projectCards = document.querySelectorAll('.project-card');

    function filterProjects() {
        const activeBtn = document.querySelector('.filter-btn.active');
        const selectedCategory = activeBtn ? activeBtn.getAttribute('data-category').toLowerCase() : 'all';
        const searchQuery = searchInput ? searchInput.value.trim().toLowerCase() : '';

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
            } else {
                card.style.display = 'none';
                card.style.opacity = '0';
            }
        });

        // Show empty state if no projects match
        const visibleCards = Array.from(projectCards).filter(c => c.style.display !== 'none');
        const emptyNotice = document.querySelector('#noProjectsNotice');
        if (emptyNotice) {
            emptyNotice.style.display = visibleCards.length === 0 ? 'block' : 'none';
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

    // 3. Tab Switcher for Dashboard
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

    // 4. Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 6000);
    }
});
