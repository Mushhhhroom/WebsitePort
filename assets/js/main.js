/**
 * Enhanced Portfolio Client-Side Logic & Interactive Micro-Physics
 * Jairus John Valdez - Computer Science Portfolio
 * Art Direction: Swiss Industrial Precision & Obsidian Kinetic
 */

document.addEventListener('DOMContentLoaded', () => {
    // ==========================================================
    // 1. Web Audio API Micro-Synthesizer (Pure JS, Opt-In)
    // ==========================================================
    const SoundEngine = {
        enabled: false,
        ctx: null,
        init() {
            this.enabled = localStorage.getItem('portfolio_audio_fx') === 'enabled';
            this.updateIcons();
        },
        toggle() {
            this.enabled = !this.enabled;
            localStorage.setItem('portfolio_audio_fx', this.enabled ? 'enabled' : 'disabled');
            this.updateIcons();
            if (this.enabled) {
                this.playTone(600, 'sine', 0.06, 0.04);
                setTimeout(() => this.playTone(900, 'sine', 0.08, 0.05), 50);
                showHudToast('Audio Haptics: Enabled 🔊', '⚡');
            } else {
                showHudToast('Audio Haptics: Muted 🔇', '⚡');
            }
            return this.enabled;
        },
        updateIcons() {
            const icons = document.querySelectorAll('.js-audio-icon');
            const labels = document.querySelectorAll('.js-audio-label');
            const btns = document.querySelectorAll('.js-audio-toggle-btn');
            icons.forEach(el => { el.textContent = this.enabled ? '🔊' : '🔇'; });
            labels.forEach(el => {
                el.textContent = this.enabled ? 'Toggle Audio Haptics (Active)' : 'Toggle Audio Haptics (Currently Off)';
            });
            btns.forEach(btn => {
                if (this.enabled) btn.classList.add('active');
                else btn.classList.remove('active');
            });
        },
        playTone(freq, type = 'sine', duration = 0.04, gainVal = 0.02) {
            if (!this.enabled) return;
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                if (!this.ctx) this.ctx = new AudioCtx();
                if (this.ctx.state === 'suspended') this.ctx.resume();

                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
                gain.gain.setValueAtTime(gainVal, this.ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + duration);
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start();
                osc.stop(this.ctx.currentTime + duration);
            } catch (e) {}
        },
        click() { this.playTone(850, 'sine', 0.03, 0.02); },
        open() {
            this.playTone(450, 'triangle', 0.05, 0.03);
            setTimeout(() => this.playTone(720, 'sine', 0.06, 0.03), 40);
        },
        close() { this.playTone(380, 'sine', 0.04, 0.02); },
        success() {
            this.playTone(523.25, 'sine', 0.05, 0.03);
            setTimeout(() => this.playTone(659.25, 'sine', 0.05, 0.03), 50);
            setTimeout(() => this.playTone(783.99, 'sine', 0.08, 0.035), 100);
        }
    };
    SoundEngine.init();

    // Audio toggle listeners
    document.querySelectorAll('.js-audio-toggle-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            SoundEngine.toggle();
        });
    });

    // ==========================================================
    // 2. Global HUD Toast Notification Dispatcher
    // ==========================================================
    let toastTimeout = null;
    function showHudToast(message, icon = '✓', duration = 2500) {
        const toast = document.getElementById('hudToast');
        const msgEl = document.getElementById('hudToastMsg');
        const iconEl = document.getElementById('hudToastIcon');
        if (!toast || !msgEl) return;

        if (iconEl) iconEl.textContent = icon;
        msgEl.textContent = message;

        toast.classList.add('is-active');
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('is-active');
        }, duration);
    }

    // ==========================================================
    // 3. Mobile Drawer Navigation & Backdrop Controls
    // ==========================================================
    const mobileToggle = document.getElementById('mobileToggle') || document.querySelector('.mobile-toggle');
    const navContainer = document.getElementById('navContainer') || document.querySelector('.nav-container');
    const navBackdrop = document.getElementById('navBackdrop') || document.querySelector('.nav-backdrop');
    const navLinks = document.querySelectorAll('.nav-link, .nav-btn-admin, .nav-btn-telemetry, .nav-cmd-trigger');
    const siteHeader = document.querySelector('.site-header');

    function openMobileMenu() {
        if (!mobileToggle || !navContainer) return;
        SoundEngine.open();
        mobileToggle.classList.add('is-active');
        mobileToggle.setAttribute('aria-expanded', 'true');
        mobileToggle.setAttribute('aria-label', 'Close navigation menu');
        navContainer.classList.add('is-open');
        if (navBackdrop) navBackdrop.classList.add('is-visible');
        document.body.classList.add('menu-open');

        setTimeout(() => {
            const firstLink = navContainer.querySelector('a, button');
            if (firstLink) firstLink.focus();
        }, 50);
    }

    function closeMobileMenu(returnFocus = true) {
        if (!mobileToggle || !navContainer) return;
        SoundEngine.close();
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
            if (mobileToggle.classList.contains('is-active')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
    }

    if (navBackdrop) {
        navBackdrop.addEventListener('click', () => closeMobileMenu(true));
    }

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                closeMobileMenu(false);
            }
        });
    });

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

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileToggle && mobileToggle.classList.contains('is-active')) {
            closeMobileMenu(true);
        }
    });

    // Header Scroll Effect (Compact Glass)
    if (siteHeader) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                siteHeader.classList.add('is-scrolled');
            } else {
                siteHeader.classList.remove('is-scrolled');
            }
        }, { passive: true });
    }

    // ==========================================================
    // 4. Project Filtering & Instant Search (projects.php)
    // ==========================================================
    const filterButtons = document.querySelectorAll('.filter-btn');
    const searchInput = document.querySelector('#projectSearch');
    const searchClearBtn = document.querySelector('#projectSearchClear');
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

        const emptyNotice = document.querySelector('#noProjectsNotice');
        if (emptyNotice) {
            emptyNotice.style.display = visibleCount === 0 ? 'block' : 'none';
        }

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
                SoundEngine.click();
                filterProjects();
            });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            filterProjects();
            if (searchClearBtn) {
                searchClearBtn.style.display = searchInput.value.length > 0 ? 'inline-block' : 'none';
            }
        });
    }

    if (searchClearBtn && searchInput) {
        searchClearBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchClearBtn.style.display = 'none';
            searchInput.focus();
            filterProjects();
            SoundEngine.click();
        });
    }

    // ==========================================================
    // 5. Tab Switcher for Dashboard
    // ==========================================================
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

                SoundEngine.click();
                history.replaceState(null, null, `#${targetTab}`);
            });
        });

        const currentHash = window.location.hash.replace('#', '');
        if (currentHash) {
            const matchedBtn = document.querySelector(`.dashboard-tabs .tab-btn[data-tab="${currentHash}"]`);
            if (matchedBtn) {
                matchedBtn.click();
            }
        }
    }

    // ==========================================================
    // 6. Auto-Dismiss Alerts after 6 Seconds
    // ==========================================================
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

    // ==========================================================
    // 7. Interactive Contact Form Validation & Character Counter
    // ==========================================================
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const messageInput = document.getElementById('message');
        const consentCheckbox = document.getElementById('privacyConsent');
        const submitBtn = document.getElementById('contactSubmitBtn');
        const counterEl = document.getElementById('messageCounter');

        const nameError = document.getElementById('nameError');
        const emailError = document.getElementById('emailError');
        const messageError = document.getElementById('messageError');
        const consentError = document.getElementById('consentError');

        // Real-time character count
        if (messageInput && counterEl) {
            function updateCharCount() {
                const len = messageInput.value.length;
                counterEl.textContent = `${len} / 1000`;
                if (len > 900) {
                    counterEl.style.color = 'var(--accent-amber)';
                } else {
                    counterEl.style.color = 'var(--text-muted)';
                }
            }
            messageInput.addEventListener('input', updateCharCount);
            updateCharCount();
        }

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

        contactForm.addEventListener('submit', (e) => {
            const isNameValid = validateName();
            const isEmailValid = validateEmail();
            const isMessageValid = validateMessage();
            const isConsentValid = validateConsent();

            if (!isNameValid || !isEmailValid || !isMessageValid || !isConsentValid) {
                e.preventDefault();
                const firstInvalid = contactForm.querySelector('.is-invalid, [aria-invalid="true"]');
                if (firstInvalid && typeof firstInvalid.focus === 'function') {
                    firstInvalid.focus();
                }
                return false;
            }

            // UX Loading indicator
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>⏳ Sending Message...</span>';
            }

            if (window.portfolioAnalytics && typeof window.portfolioAnalytics.trackEvent === 'function') {
                window.portfolioAnalytics.trackEvent('contact_form_submit', {
                    category: 'engagement',
                    timestamp: Date.now()
                });
            }
        });
    }

    // ==========================================================
    // 8. System Telemetry Modal Controller (Native <dialog>)
    // ==========================================================
    const systemModal = document.getElementById('systemModal');
    const closeSystemModalBtn = document.getElementById('closeSystemModalBtn');
    const dismissSystemModalBtn = document.getElementById('dismissSystemModalBtn');
    const systemModalTriggers = document.querySelectorAll('.js-system-modal-trigger');

    let previousActiveElement = null;

    function openSystemModal() {
        if (!systemModal) return;
        SoundEngine.open();
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
        SoundEngine.close();

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
        systemModal.addEventListener('click', (e) => {
            if (e.target === systemModal) closeSystemModal();
        });

        systemModal.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSystemModal();
        });
    }

    // ==========================================================
    // 9. Interactive Command Palette Controller (Ctrl+K)
    // ==========================================================
    const cmdPalette = document.getElementById('commandPalette');
    const cmdSearchInput = document.getElementById('cmdSearchInput');
    const cmdResultsList = document.getElementById('cmdResultsList');
    const cmdDismissBtn = document.getElementById('cmdDismissBtn');
    const cmdEmptyState = document.getElementById('cmdEmptyState');
    const cmdTriggers = document.querySelectorAll('.js-cmd-palette-trigger');

    let cmdActiveIndex = -1;

    function getVisibleCmdItems() {
        if (!cmdResultsList) return [];
        return Array.from(cmdResultsList.querySelectorAll('.cmd-item')).filter(
            item => item.style.display !== 'none'
        );
    }

    function setCmdActiveItem(index) {
        const items = getVisibleCmdItems();
        items.forEach(el => el.classList.remove('is-selected'));
        if (items.length === 0) {
            cmdActiveIndex = -1;
            return;
        }
        if (index < 0) index = items.length - 1;
        if (index >= items.length) index = 0;
        cmdActiveIndex = index;
        const activeItem = items[cmdActiveIndex];
        if (activeItem) {
            activeItem.classList.add('is-selected');
            activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    function openCommandPalette() {
        if (!cmdPalette) return;
        SoundEngine.open();
        if (typeof cmdPalette.showModal === 'function') {
            cmdPalette.showModal();
        } else {
            cmdPalette.setAttribute('open', '');
        }
        if (cmdSearchInput) {
            cmdSearchInput.value = '';
            filterCommandPalette('');
            setTimeout(() => cmdSearchInput.focus(), 60);
        }
        setCmdActiveItem(0);
    }

    function closeCommandPalette() {
        if (!cmdPalette) return;
        SoundEngine.close();
        if (typeof cmdPalette.close === 'function') {
            cmdPalette.close();
        } else {
            cmdPalette.removeAttribute('open');
        }
    }

    function filterCommandPalette(query) {
        if (!cmdResultsList) return;
        const q = query.toLowerCase().trim();
        const items = cmdResultsList.querySelectorAll('.cmd-item');
        const groups = cmdResultsList.querySelectorAll('.cmd-group-title');
        let matchCount = 0;

        items.forEach(item => {
            const title = (item.querySelector('.cmd-item-title')?.textContent || '').toLowerCase();
            const desc = (item.querySelector('.cmd-item-desc')?.textContent || '').toLowerCase();
            const keywords = (item.getAttribute('data-keywords') || '').toLowerCase();
            const key = (item.getAttribute('data-key') || '').toLowerCase();

            const isMatch = !q || title.includes(q) || desc.includes(q) || keywords.includes(q) || key === q;
            if (isMatch) {
                item.style.display = 'flex';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
        });

        groups.forEach(group => {
            let next = group.nextElementSibling;
            let hasVisible = false;
            while (next && !next.classList.contains('cmd-group-title')) {
                if (next.classList.contains('cmd-item') && next.style.display !== 'none') {
                    hasVisible = true;
                    break;
                }
                next = next.nextElementSibling;
            }
            group.style.display = hasVisible ? 'block' : 'none';
        });

        if (cmdEmptyState) {
            cmdEmptyState.style.display = matchCount === 0 ? 'block' : 'none';
        }
        setCmdActiveItem(0);
    }

    if (cmdTriggers.length > 0) {
        cmdTriggers.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openCommandPalette();
            });
        });
    }

    if (cmdDismissBtn) {
        cmdDismissBtn.addEventListener('click', closeCommandPalette);
    }

    if (cmdPalette) {
        cmdPalette.addEventListener('click', (e) => {
            if (e.target === cmdPalette) closeCommandPalette();
        });
        cmdPalette.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeCommandPalette();
        });
    }

    if (cmdSearchInput) {
        cmdSearchInput.addEventListener('input', (e) => {
            filterCommandPalette(e.target.value);
            SoundEngine.click();
        });

        cmdSearchInput.addEventListener('keydown', (e) => {
            const items = getVisibleCmdItems();
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setCmdActiveItem(cmdActiveIndex + 1);
                SoundEngine.click();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setCmdActiveItem(cmdActiveIndex - 1);
                SoundEngine.click();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const active = items[cmdActiveIndex];
                if (active) {
                    active.click();
                }
            }
        });
    }

    // Command actions execution
    document.querySelectorAll('.js-cmd-action').forEach(actionBtn => {
        actionBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const action = actionBtn.getAttribute('data-action');
            closeCommandPalette();

            switch (action) {
                case 'open-telemetry':
                    openSystemModal();
                    break;
                case 'open-cookies':
                    const cookieBtn = document.getElementById('openCookieSettingsBtn');
                    if (cookieBtn) cookieBtn.click();
                    break;
                case 'toggle-audio':
                    SoundEngine.toggle();
                    break;
                case 'copy-email':
                    const email = 'Valdez.jairusjohn.deleste@gmail.com';
                    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                        navigator.clipboard.writeText(email).then(() => {
                            SoundEngine.success();
                            showHudToast('Copied to clipboard: ' + email, '📋');
                        }).catch(() => {
                            showHudToast('Email: ' + email, '✉️');
                        });
                    } else {
                        showHudToast('Email: ' + email, '✉️');
                    }
                    break;
                case 'scroll-top':
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    SoundEngine.click();
                    showHudToast('Viewport centered to summit', '🔝');
                    break;
            }
        });
    });

    // Global keyboard hotkeys: Ctrl+K / Cmd+K / / / T
    document.addEventListener('keydown', (e) => {
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const isEditing = activeTag === 'input' || activeTag === 'textarea' || document.activeElement?.isContentEditable;

        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            if (cmdPalette && cmdPalette.open) {
                closeCommandPalette();
            } else {
                openCommandPalette();
            }
            return;
        }

        if (isEditing) return;

        if (e.key === '/') {
            e.preventDefault();
            openCommandPalette();
        }

        if (e.key === 't' || e.key === 'T') {
            if (!cmdPalette?.open && !systemModal?.open) {
                e.preventDefault();
                openSystemModal();
            }
        }
    });

    // ==========================================================
    // 10. Telemetry Reading HUD & Section Spy Observer
    // ==========================================================
    const hudSectionLabel = document.getElementById('hudSectionLabel');
    const hudProgressVal = document.getElementById('hudProgressVal');
    const hudScrollTopBtn = document.getElementById('hudScrollTopBtn');

    function updateScrollTelemetry() {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const progress = scrollHeight > 0 ? Math.min(100, Math.max(0, Math.round((scrollTop / scrollHeight) * 100))) : 0;

        if (hudProgressVal) {
            hudProgressVal.textContent = `${progress}%`;
        }

        if (hudScrollTopBtn) {
            if (scrollTop > 240) {
                hudScrollTopBtn.classList.add('is-visible');
            } else {
                hudScrollTopBtn.classList.remove('is-visible');
            }
        }
    }

    window.addEventListener('scroll', updateScrollTelemetry, { passive: true });
    updateScrollTelemetry();

    if (hudScrollTopBtn) {
        hudScrollTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            SoundEngine.click();
        });
    }

    // Section Spy Observer
    const trackedSections = document.querySelectorAll('[data-section-name]');
    if (trackedSections.length > 0 && 'IntersectionObserver' in window) {
        const spyObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && hudSectionLabel) {
                    const secName = entry.target.getAttribute('data-section-name');
                    if (secName) {
                        hudSectionLabel.textContent = `LOC // ${secName}`;
                    }
                }
            });
        }, {
            rootMargin: '-20% 0px -60% 0px',
            threshold: 0
        });

        trackedSections.forEach(sec => spyObserver.observe(sec));
    }
});
