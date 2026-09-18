<?php
/**
 * Cookie Policy (cookies.php)
 * Portfolio Project - Jairus John Valdez
 * Compliant with ePrivacy Directive (EU), GDPR, and global consent frameworks.
 */

$page_title = 'Cookie Policy | Jairus John Valdez';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section" style="padding-top: 60px; padding-bottom: 80px;">
    <div class="container">
        <div class="legal-wrapper" style="max-width: 860px; margin: 0 auto;">
            
            <header class="legal-header" style="margin-bottom: 40px; border-bottom: 1px solid var(--border-color); padding-bottom: 24px;">
                <div class="section-subtitle">COOKIE COMPLIANCE &amp; CONTROL</div>
                <h1 style="font-size: 2.4rem; font-weight: 800; margin-top: 8px; margin-bottom: 12px; color: var(--text-primary);">
                    Cookie Policy
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    <strong>Effective Date:</strong> September 18, 2026 &bull; <strong>Last Updated:</strong> September 18, 2026
                </p>
            </header>

            <div class="legal-body" style="color: var(--text-secondary); line-height: 1.8; font-size: 1rem;">
                
                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        1. What Are Cookies &amp; Web Storage?
                    </h2>
                    <p style="margin-bottom: 14px;">
                        Cookies are small text files that websites store on your computer or mobile device when you visit them. Alongside cookies, modern web applications utilize <code>localStorage</code> to store client-side preferences without transmitting unnecessary data in every HTTP header.
                    </p>
                    <p>
                        This website operates under an <strong>opt-in consent model</strong>: non-essential tracking cookies and analytics tags are blocked by default until you explicitly grant consent.
                    </p>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        2. Manage Your Cookie Preferences
                    </h2>
                    <p style="margin-bottom: 16px;">
                        You have complete control over non-essential cookies. You can inspect or update your choices at any time:
                    </p>
                    <div style="background: rgba(14, 21, 37, 0.85); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                        <div>
                            <strong style="color: var(--text-primary); font-size: 1.05rem; display: block; margin-bottom: 4px;">Cookie Consent Preferences</strong>
                            <span style="font-size: 0.875rem; color: var(--text-muted);">View status, toggle optional categories, or withdraw consent.</span>
                        </div>
                        <button type="button" id="cookiePolicySettingsBtn" class="btn btn-primary btn-sm js-cookie-settings-trigger">
                            ⚙ Open Cookie Settings
                        </button>
                    </div>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        3. Detailed Cookie Inventory
                    </h2>
                    <p style="margin-bottom: 16px;">
                        Below is a complete description of the cookies and local storage tokens utilized on this portfolio:
                    </p>

                    <div style="overflow-x: auto; margin-bottom: 24px;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; background: rgba(11, 17, 32, 0.6); border: 1px solid var(--border-color); border-radius: 8px;">
                            <thead>
                                <tr style="background: rgba(255, 255, 255, 0.04); border-bottom: 1px solid var(--border-color);">
                                    <th style="padding: 12px 14px; color: var(--text-primary);">Cookie / Key</th>
                                    <th style="padding: 12px 14px; color: var(--text-primary);">Category</th>
                                    <th style="padding: 12px 14px; color: var(--text-primary);">Purpose</th>
                                    <th style="padding: 12px 14px; color: var(--text-primary);">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td style="padding: 12px 14px; font-family: var(--font-mono); color: var(--accent-cyan);">PHPSESSID</td>
                                    <td style="padding: 12px 14px;"><span class="tag-badge tag-badge-green">Strictly Necessary</span></td>
                                    <td style="padding: 12px 14px;">Maintains server session state and admin authentication.</td>
                                    <td style="padding: 12px 14px;">Session / 24h</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td style="padding: 12px 14px; font-family: var(--font-mono); color: var(--accent-cyan);">csrf_token</td>
                                    <td style="padding: 12px 14px;"><span class="tag-badge tag-badge-green">Strictly Necessary</span></td>
                                    <td style="padding: 12px 14px;">Cryptographic protection against Cross-Site Request Forgery.</td>
                                    <td style="padding: 12px 14px;">Session</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td style="padding: 12px 14px; font-family: var(--font-mono); color: var(--accent-cyan);">portfolio_cookie_consent_v1</td>
                                    <td style="padding: 12px 14px;"><span class="tag-badge tag-badge-green">Strictly Necessary</span></td>
                                    <td style="padding: 12px 14px;">Stores your cookie preferences (Necessary, Analytics, Marketing).</td>
                                    <td style="padding: 12px 14px;">180 Days</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td style="padding: 12px 14px; font-family: var(--font-mono); color: var(--accent-cyan);">_ga / _ga_*</td>
                                    <td style="padding: 12px 14px;"><span class="tag-badge" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd;">Performance &amp; Analytics</span></td>
                                    <td style="padding: 12px 14px;">Anonymous page usage and traffic measurement (Opt-In Only).</td>
                                    <td style="padding: 12px 14px;">2 Years (If accepted)</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; font-family: var(--font-mono); color: var(--accent-cyan);">_fbp / marketing</td>
                                    <td style="padding: 12px 14px;"><span class="tag-badge" style="background: rgba(236, 72, 153, 0.15); color: #f472b6;">Marketing &amp; Targeting</span></td>
                                    <td style="padding: 12px 14px;">Embedded social media preview and conversion tracking (Opt-In Only).</td>
                                    <td style="padding: 12px 14px;">90 Days (If accepted)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        4. How to Control Cookies in Your Browser
                    </h2>
                    <p style="margin-bottom: 12px;">
                        In addition to the on-site preference manager, all major modern web browsers allow you to modify cookie storage settings:
                    </p>
                    <ul style="margin-left: 24px; list-style-type: disc;">
                        <li style="margin-bottom: 8px;"><strong>Google Chrome:</strong> Settings &rarr; Privacy and Security &rarr; Cookies and other site data.</li>
                        <li style="margin-bottom: 8px;"><strong>Mozilla Firefox:</strong> Settings &rarr; Privacy &amp; Security &rarr; Cookies and Site Data.</li>
                        <li style="margin-bottom: 8px;"><strong>Apple Safari:</strong> Preferences &rarr; Privacy &rarr; Manage Website Data.</li>
                        <li><strong>Microsoft Edge:</strong> Settings &rarr; Cookies and Site Permissions &rarr; Manage and delete cookies and site data.</li>
                    </ul>
                </section>

                <section class="legal-section" style="border-top: 1px solid var(--border-color); padding-top: 24px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        5. Questions &amp; Support
                    </h2>
                    <p>
                        For inquiries concerning this Cookie Policy, contact:<br>
                        <strong>Jairus John D. Valdez</strong><br>
                        Email: <a href="mailto:Valdez.jairusjohn.deleste@gmail.com" class="legal-link">Valdez.jairusjohn.deleste@gmail.com</a>
                    </p>
                </section>

            </div>

            <div style="margin-top: 40px; display: flex; gap: 14px; flex-wrap: wrap;">
                <a href="privacy.php" class="btn btn-outline btn-sm">Read Privacy Policy &rarr;</a>
                <a href="terms.php" class="btn btn-outline btn-sm">Read Terms &amp; Conditions &rarr;</a>
                <a href="contact.php" class="btn btn-primary btn-sm">Contact Jairus &rarr;</a>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
