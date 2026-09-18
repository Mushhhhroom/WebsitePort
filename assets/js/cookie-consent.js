/**
 * Granular Cookie Consent & Analytics Privacy Gate
 * Portfolio Project - Jairus John Valdez
 * Compliant with WCAG 2.1 AA, GDPR Opt-in, and ePrivacy Directive
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'portfolio_cookie_consent_v1';
    const CONSENT_EXPIRY_DAYS = 180;
    const CONSENT_VERSION = '1.0';

    // Default state: Only strictly necessary is true. All others false.
    const defaultConsent = {
        necessary: true,
        analytics: false,
        marketing: false,
        timestamp: null,
        version: CONSENT_VERSION
    };

    /**
     * Read consent from localStorage with expiration verification
     */
    function loadStoredConsent() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') return null;

            // Check expiration (180 days)
            if (parsed.timestamp) {
                const ageMs = Date.now() - parsed.timestamp;
                const maxAgeMs = CONSENT_EXPIRY_DAYS * 24 * 60 * 60 * 1000;
                if (ageMs > maxAgeMs) {
                    localStorage.removeItem(STORAGE_KEY);
                    return null;
                }
            }
            return {
                necessary: true,
                analytics: Boolean(parsed.analytics),
                marketing: Boolean(parsed.marketing),
                timestamp: parsed.timestamp || Date.now(),
                version: parsed.version || CONSENT_VERSION
            };
        } catch (e) {
            return null;
        }
    }

    /**
     * Save consent to localStorage and cookie
     */
    function saveConsent(consent) {
        const payload = {
            necessary: true,
            analytics: Boolean(consent.analytics),
            marketing: Boolean(consent.marketing),
            timestamp: Date.now(),
            version: CONSENT_VERSION
        };

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
        } catch (e) {
            // Storage quota or disabled
        }

        // Set secure cookie for server awareness
        const maxAge = CONSENT_EXPIRY_DAYS * 24 * 60 * 60;
        const isSecure = window.location.protocol === 'https:';
        document.cookie = `portfolio_consent=${encodeURIComponent(JSON.stringify(payload))}; max-age=${maxAge}; path=/; SameSite=Lax${isSecure ? '; Secure' : ''}`;

        // Notify application
        document.dispatchEvent(new CustomEvent('portfolioConsentUpdated', { detail: payload }));
        activateConsentedScripts(payload);
        return payload;
    }

    /**
     * Dynamically activates scripts tagged with type="text/plain" and data-consent-category
     */
    function activateConsentedScripts(consent) {
        const scripts = document.querySelectorAll('script[type="text/plain"][data-consent-category]');
        scripts.forEach(script => {
            const category = script.getAttribute('data-consent-category');
            if (consent[category]) {
                const newScript = document.createElement('script');
                Array.from(script.attributes).forEach(attr => {
                    if (attr.name !== 'type' && attr.name !== 'data-consent-category') {
                        newScript.setAttribute(attr.name, attr.value);
                    }
                });
                newScript.type = 'text/javascript';
                newScript.innerHTML = script.innerHTML;
                script.parentNode.replaceChild(newScript, script);
            }
        });
    }

    // Initialize Global Consent API
    window.portfolioConsent = {
        getConsent() {
            return loadStoredConsent() || { ...defaultConsent };
        },
        hasGivenConsent() {
            return loadStoredConsent() !== null;
        },
        isAllowed(category) {
            if (category === 'necessary') return true;
            const current = loadStoredConsent();
            return current ? Boolean(current[category]) : false;
        },
        setConsent(categories) {
            return saveConsent(categories);
        },
        openSettings() {
            openPreferenceModal();
        }
    };

    // Initialize Privacy-Gated Analytics API
    window.portfolioAnalytics = {
        trackEvent(eventName, params = {}) {
            if (!window.portfolioConsent.isAllowed('analytics')) {
                // Strictly blocked by default until explicit consent is granted
                return false;
            }

            // If third-party tools (Google Analytics gtag, PostHog, Plausible) are present, forward safely
            if (typeof window.gtag === 'function') {
                window.gtag('event', eventName, params);
            }
            if (window.posthog && typeof window.posthog.capture === 'function') {
                window.posthog.capture(eventName, params);
            }
            return true;
        },
        pageView(path = window.location.pathname) {
            if (!window.portfolioConsent.isAllowed('analytics')) {
                return false;
            }
            if (typeof window.gtag === 'function') {
                window.gtag('event', 'page_view', { page_path: path });
            }
            return true;
        }
    };

    // DOM & Modal Focus Management Variables
    let lastActiveElement = null;

    function getFocusableElements(container) {
        if (!container) return [];
        return Array.from(container.querySelectorAll(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        ));
    }

    function trapFocus(modal, event) {
        if (event.key !== 'Tab') return;
        const focusables = getFocusableElements(modal);
        if (focusables.length === 0) return;

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey) {
            if (document.activeElement === first) {
                event.preventDefault();
                last.focus();
            }
        } else {
            if (document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    }

    function openPreferenceModal() {
        const backdrop = document.getElementById('cookieModalBackdrop');
        const modal = document.getElementById('cookieModal');
        const analyticsToggle = document.getElementById('cookieCategoryAnalytics');
        const marketingToggle = document.getElementById('cookieCategoryMarketing');
        if (!backdrop || !modal) return;

        lastActiveElement = document.activeElement;

        // Pre-populate toggles from current consent state
        const current = window.portfolioConsent.getConsent();
        if (analyticsToggle) analyticsToggle.checked = Boolean(current.analytics);
        if (marketingToggle) marketingToggle.checked = Boolean(current.marketing);

        backdrop.style.display = 'flex';
        document.body.classList.add('cookie-modal-open');

        // Focus modal or first interactive element
        setTimeout(() => {
            const focusables = getFocusableElements(modal);
            if (focusables.length > 0) {
                focusables[0].focus();
            } else {
                modal.focus();
            }
        }, 50);

        // Hide banner if visible while modal is open
        const banner = document.getElementById('cookieConsentBanner');
        if (banner) banner.style.display = 'none';
    }

    function closePreferenceModal() {
        const backdrop = document.getElementById('cookieModalBackdrop');
        if (!backdrop) return;
        backdrop.style.display = 'none';
        document.body.classList.remove('cookie-modal-open');

        // Restore focus for screen readers and keyboard users
        if (lastActiveElement && typeof lastActiveElement.focus === 'function') {
            lastActiveElement.focus();
        }
    }

    // Attach Event Listeners on DOM Ready
    document.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('cookieConsentBanner');
        const acceptAllBtn = document.getElementById('cookieAcceptAllBtn');
        const rejectNonEssentialBtn = document.getElementById('cookieRejectNonEssentialBtn');
        const customizeBtn = document.getElementById('cookieCustomizeBtn');

        const modalBackdrop = document.getElementById('cookieModalBackdrop');
        const modal = document.getElementById('cookieModal');
        const modalCloseBtn = document.getElementById('cookieModalCloseBtn');
        const savePreferencesBtn = document.getElementById('cookieSavePreferencesBtn');
        const modalAcceptAllBtn = document.getElementById('cookieModalAcceptAllBtn');

        const hasStored = window.portfolioConsent.hasGivenConsent();

        // 1. Show banner only if no consent previously recorded
        if (!hasStored && banner) {
            setTimeout(() => {
                banner.style.display = 'block';
            }, 500);
        } else if (hasStored) {
            // Activate any consented scripts on page load
            activateConsentedScripts(window.portfolioConsent.getConsent());
        }

        // 2. Banner Actions
        if (acceptAllBtn) {
            acceptAllBtn.addEventListener('click', () => {
                saveConsent({ necessary: true, analytics: true, marketing: true });
                if (banner) banner.style.display = 'none';
            });
        }

        if (rejectNonEssentialBtn) {
            rejectNonEssentialBtn.addEventListener('click', () => {
                saveConsent({ necessary: true, analytics: false, marketing: false });
                if (banner) banner.style.display = 'none';
            });
        }

        if (customizeBtn) {
            customizeBtn.addEventListener('click', () => {
                openPreferenceModal();
            });
        }

        // 3. Modal Actions
        if (modalCloseBtn) {
            modalCloseBtn.addEventListener('click', () => {
                closePreferenceModal();
                // If user hasn't saved consent yet, keep banner visible
                if (!window.portfolioConsent.hasGivenConsent() && banner) {
                    banner.style.display = 'block';
                }
            });
        }

        if (modalBackdrop) {
            modalBackdrop.addEventListener('click', (e) => {
                if (e.target === modalBackdrop) {
                    closePreferenceModal();
                    if (!window.portfolioConsent.hasGivenConsent() && banner) {
                        banner.style.display = 'block';
                    }
                }
            });
        }

        if (modalAcceptAllBtn) {
            modalAcceptAllBtn.addEventListener('click', () => {
                saveConsent({ necessary: true, analytics: true, marketing: true });
                closePreferenceModal();
                if (banner) banner.style.display = 'none';
            });
        }

        if (savePreferencesBtn) {
            savePreferencesBtn.addEventListener('click', () => {
                const analyticsToggle = document.getElementById('cookieCategoryAnalytics');
                const marketingToggle = document.getElementById('cookieCategoryMarketing');
                saveConsent({
                    necessary: true,
                    analytics: analyticsToggle ? analyticsToggle.checked : false,
                    marketing: marketingToggle ? marketingToggle.checked : false
                });
                closePreferenceModal();
                if (banner) banner.style.display = 'none';
            });
        }

        // 4. Modal Accessibility: Escape Key & Focus Trap
        document.addEventListener('keydown', (e) => {
            if (modalBackdrop && modalBackdrop.style.display === 'flex') {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closePreferenceModal();
                    if (!window.portfolioConsent.hasGivenConsent() && banner) {
                        banner.style.display = 'block';
                    }
                } else if (e.key === 'Tab') {
                    trapFocus(modal, e);
                }
            }
        });

        // 5. Global Triggers (e.g. from footer button or policy page)
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.js-cookie-settings-trigger, #openCookieSettings, #openCookieSettingsBtn');
            if (trigger) {
                e.preventDefault();
                openPreferenceModal();
            }
        });
    });
})();
