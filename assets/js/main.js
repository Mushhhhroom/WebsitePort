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
        mobileToggle.setAttribute('aria-label', 'Close navigation menu');
        navContainer.classList.add('is-open');
        if (navBackdrop) navBackdrop.classList.add('is-visible');
        document.body.classList.add('menu-open');

        // Focus first navigation link
        setTimeout(() => {
            const firstLink = navContainer.querySelector('a');
            if (firstLink) firstLink.focus();
        }, 50);
    }

    function closeMobileMenu(returnFocus = true) {
        if (!mobileToggle || !navContainer) return;
        const wasOpen = mobileToggle.classList.contains('is-active');
        mobileToggle.classList.remove('is-active');
        mobileToggle.setAttribute('aria-expanded', 'false');
        mobileToggle.setAttribute('aria-label', 'Open navigation menu');
        navContainer.classList.remove('is-open');
        if (navBackdrop) navBackdrop.classList.remove('is-visible');
        document.body.classList.remove('menu-open');

        if (wasOpen && returnFocus && typeof mobileToggle.focus === 'function') {
            mobileToggle.focus();
        }
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
        navBackdrop.addEventListener('click', () => closeMobileMenu(true));
    }

    // Close mobile menu when clicking any nav link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                closeMobileMenu(false);
            }
        });
    });

    // Mobile drawer focus trap & Escape key listener
    if (navContainer) {
        navContainer.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeMobileMenu(true);
                return;
            }
            if (e.key !== 'Tab') return;
            const focusables = Array.from(navContainer.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])'));
            if (focusables.length === 0) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });
    }

    // Global Escape Key to close mobile menu
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileToggle && mobileToggle.classList.contains('is-active')) {
            closeMobileMenu(true);
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
    const searchStatus = document.querySelector('#projectSearchStatus');

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

        // Dynamic screen reader announcement
        if (searchStatus) {
            searchStatus.textContent = `Showing ${visibleCount} of ${projectCards.length} projects.`;
        }
    }

    if (filterButtons.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                filterButtons.forEach(b => {
                    b.classList.remove('active');
                    b.setAttribute('aria-selected', 'false');
                });
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
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

    // 6. WCAG 2.1 AA Compliant Client-Side Validation for Contact Form
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const messageInput = document.getElementById('message');
        const consentCheckbox = document.getElementById('privacyConsent');

        const nameError = document.getElementById('nameError');
        const emailError = document.getElementById('emailError');
        const messageError = document.getElementById('messageError');
        const consentError = document.getElementById('consentError');

        function setFieldError(input, errorEl, message) {
            if (!input || !errorEl) return;
            input.classList.add('is-invalid');
            input.setAttribute('aria-invalid', 'true');
            errorEl.textContent = message;
            errorEl.classList.add('is-visible');
        }

        function clearFieldError(input, errorEl) {
            if (!input || !errorEl) return;
            input.classList.remove('is-invalid');
            input.setAttribute('aria-invalid', 'false');
            errorEl.textContent = '';
            errorEl.classList.remove('is-visible');
        }

        function validateName() {
            if (!nameInput) return true;
            const val = nameInput.value.trim();
            if (val.length < 2) {
                setFieldError(nameInput, nameError, 'Please enter your full name (at least 2 characters).');
                return false;
            }
            clearFieldError(nameInput, nameError);
            return true;
        }

        function validateEmail() {
            if (!emailInput) return true;
            const val = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!val || !emailRegex.test(val)) {
                setFieldError(emailInput, emailError, 'Please provide a valid email address (e.g. name@domain.com).');
                return false;
            }
            clearFieldError(emailInput, emailError);
            return true;
        }

        function validateMessage() {
            if (!messageInput) return true;
            const val = messageInput.value.trim();
            if (val.length < 10) {
                setFieldError(messageInput, messageError, 'Please write a message of at least 10 characters.');
                return false;
            }
            clearFieldError(messageInput, messageError);
            return true;
        }

        function validateConsent() {
            if (!consentCheckbox) return true;
            if (!consentCheckbox.checked) {
                setFieldError(consentCheckbox, consentError, 'You must accept the Privacy Policy before sending a message.');
                return false;
            }
            clearFieldError(consentCheckbox, consentError);
            return true;
        }

        // Real-time blur and input listeners
        if (nameInput) {
            nameInput.addEventListener('blur', validateName);
            nameInput.addEventListener('input', () => {
                if (nameInput.classList.contains('is-invalid')) validateName();
            });
        }

        if (emailInput) {
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('input', () => {
                if (emailInput.classList.contains('is-invalid')) validateEmail();
            });
        }

        if (messageInput) {
            messageInput.addEventListener('blur', validateMessage);
            messageInput.addEventListener('input', () => {
                if (messageInput.classList.contains('is-invalid')) validateMessage();
            });
        }

        if (consentCheckbox) {
            consentCheckbox.addEventListener('change', validateConsent);
        }

        // Form submission listener
        contactForm.addEventListener('submit', (e) => {
            const isNameValid = validateName();
            const isEmailValid = validateEmail();
            const isMessageValid = validateMessage();
            const isConsentValid = validateConsent();

            if (!isNameValid || !isEmailValid || !isMessageValid || !isConsentValid) {
                e.preventDefault();

                // Focus the first invalid element for screen reader accessibility
                const firstInvalid = contactForm.querySelector('.is-invalid, [aria-invalid="true"]');
                if (firstInvalid && typeof firstInvalid.focus === 'function') {
                    firstInvalid.focus();
                }
                return false;
            }

            // Privacy-gated analytics event dispatch
            if (window.portfolioAnalytics && typeof window.portfolioAnalytics.trackEvent === 'function') {
                window.portfolioAnalytics.trackEvent('contact_form_submit', {
                    category: 'engagement',
                    timestamp: Date.now()
                });
            }
        });
    }

    // 7. System Telemetry Modal Controller (Native <dialog> with Accessible Controls)
    const systemModal = document.getElementById('systemModal');
    const closeSystemModalBtn = document.getElementById('closeSystemModalBtn');
    const dismissSystemModalBtn = document.getElementById('dismissSystemModalBtn');
    const systemModalTriggers = document.querySelectorAll('.js-system-modal-trigger');

    let previousActiveElement = null;

    function openSystemModal() {
        if (!systemModal) return;
        previousActiveElement = document.activeElement;

        if (typeof systemModal.showModal === 'function') {
            systemModal.showModal();
        } else {
            systemModal.setAttribute('open', '');
        }

        if (closeSystemModalBtn) {
            closeSystemModalBtn.focus();
        }
    }

    function closeSystemModal() {
        if (!systemModal) return;

        if (typeof systemModal.close === 'function') {
            systemModal.close();
        } else {
            systemModal.removeAttribute('open');
        }

        if (previousActiveElement && typeof previousActiveElement.focus === 'function') {
            previousActiveElement.focus();
        }
    }

    if (systemModalTriggers.length > 0) {
        systemModalTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                openSystemModal();
            });
        });
    }

    if (closeSystemModalBtn) {
        closeSystemModalBtn.addEventListener('click', closeSystemModal);
    }

    if (dismissSystemModalBtn) {
        dismissSystemModalBtn.addEventListener('click', closeSystemModal);
    }

    if (systemModal) {
        // Close when clicking outside dialog-frame (on backdrop)
        systemModal.addEventListener('click', (e) => {
            if (e.target === systemModal) {
                closeSystemModal();
            }
        });

        // Close on ESC key fallback
        systemModal.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeSystemModal();
            }
        });
    }
});

