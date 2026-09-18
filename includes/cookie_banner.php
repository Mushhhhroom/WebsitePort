<?php
/**
 * Accessible Cookie Consent Banner & Preference Modal Component
 * Portfolio Project - Jairus John Valdez
 * Meets WCAG 2.1 AA Standards & GDPR Opt-in Requirements
 */
?>
<!-- Cookie Consent Banner (Opt-in by default) -->
<aside id="cookieConsentBanner" 
       class="cookie-banner" 
       aria-label="Privacy and Cookie Consent" 
       role="region" 
       style="display: none;">
    <div class="container cookie-banner-inner">
        <div class="cookie-banner-text">
            <div class="cookie-banner-title">
                <span class="cookie-icon" aria-hidden="true">🍪</span>
                <strong>Privacy &amp; Cookie Choices</strong>
            </div>
            <p id="cookieBannerDesc">
                This portfolio uses necessary cookies for security and session state. With your permission, I also use privacy-first analytics to understand performance. All non-essential cookies remain blocked until you grant consent. Read the <a href="privacy.php" target="_blank" rel="noopener">Privacy Policy</a> and <a href="cookies.php" target="_blank" rel="noopener">Cookie Policy</a>.
            </p>
        </div>
        <div class="cookie-banner-actions">
            <button type="button" id="cookieAcceptAllBtn" class="btn btn-primary btn-sm">
                Accept All
            </button>
            <button type="button" id="cookieRejectNonEssentialBtn" class="btn btn-outline btn-sm">
                Reject Non-Essential
            </button>
            <button type="button" id="cookieCustomizeBtn" class="btn btn-outline btn-sm">
                Customize Preferences
            </button>
        </div>
    </div>
</aside>

<!-- Cookie Preference Modal Dialog -->
<div id="cookieModalBackdrop" class="cookie-modal-backdrop" style="display: none;">
    <div id="cookieModal" 
         class="cookie-modal" 
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="cookieModalTitle" 
         aria-describedby="cookieModalDesc" 
         tabindex="-1">
        
        <div class="cookie-modal-header">
            <div>
                <h2 id="cookieModalTitle" class="cookie-modal-title">Cookie &amp; Tracking Preferences</h2>
                <p id="cookieModalDesc" class="cookie-modal-subtitle">
                    Configure which categories of cookies you permit. Necessary cookies cannot be disabled as they are required for security.
                </p>
            </div>
            <button type="button" 
                    id="cookieModalCloseBtn" 
                    class="cookie-modal-close" 
                    aria-label="Close cookie settings">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="cookie-modal-body">
            <!-- Category 1: Necessary -->
            <div class="cookie-category-card">
                <div class="cookie-category-head">
                    <div>
                        <div class="cookie-category-title">
                            <h3>Strictly Necessary Cookies</h3>
                            <span class="tag-badge tag-badge-green">Always Active</span>
                        </div>
                        <p class="cookie-category-desc">
                            Required for fundamental site operation, CSRF protection tokens, session state, and security against brute-force attacks.
                        </p>
                    </div>
                    <div class="cookie-toggle-wrapper">
                        <input type="checkbox" 
                               id="cookieCategoryNecessary" 
                               checked 
                               disabled 
                               aria-label="Strictly Necessary Cookies (Always active)">
                        <label for="cookieCategoryNecessary" class="switch-ui disabled" aria-hidden="true"></label>
                    </div>
                </div>
            </div>

            <!-- Category 2: Analytics -->
            <div class="cookie-category-card">
                <div class="cookie-category-head">
                    <div>
                        <div class="cookie-category-title">
                            <h3>Performance &amp; Analytics</h3>
                            <span class="tag-badge" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd;">Opt-in</span>
                        </div>
                        <p class="cookie-category-desc">
                            Helps analyze traffic patterns, popular projects, and site performance anonymously. No events or telemetry fire unless enabled.
                        </p>
                    </div>
                    <div class="cookie-toggle-wrapper">
                        <input type="checkbox" 
                               id="cookieCategoryAnalytics" 
                               class="cookie-checkbox"
                               aria-label="Enable Performance and Analytics Cookies">
                        <label for="cookieCategoryAnalytics" class="switch-ui"></label>
                    </div>
                </div>
            </div>

            <!-- Category 3: Marketing -->
            <div class="cookie-category-card">
                <div class="cookie-category-head">
                    <div>
                        <div class="cookie-category-title">
                            <h3>Marketing &amp; External Embeds</h3>
                            <span class="tag-badge" style="background: rgba(236, 72, 153, 0.15); color: #f472b6;">Opt-in</span>
                        </div>
                        <p class="cookie-category-desc">
                            Controls rich external media embeds (such as video demos, interactive code sandboxes, and social cards).
                        </p>
                    </div>
                    <div class="cookie-toggle-wrapper">
                        <input type="checkbox" 
                               id="cookieCategoryMarketing" 
                               class="cookie-checkbox"
                               aria-label="Enable Marketing and External Embed Cookies">
                        <label for="cookieCategoryMarketing" class="switch-ui"></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="cookie-modal-footer">
            <button type="button" id="cookieSavePreferencesBtn" class="btn btn-primary btn-sm">
                Save My Preferences
            </button>
            <button type="button" id="cookieModalAcceptAllBtn" class="btn btn-outline btn-sm">
                Accept All Categories
            </button>
        </div>

    </div>
</div>
