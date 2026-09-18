<?php
/**
 * Privacy Policy (privacy.php)
 * Portfolio Project - Jairus John Valdez
 * Compliant with GDPR (EU), Data Privacy Act of 2012 (RA 10173, Philippines), and international privacy standards.
 */

$page_id = 'privacy';
$current_script = 'privacy.php';
$page_title = 'Privacy Policy | Jairus John Valdez';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section" style="padding-top: 60px; padding-bottom: 80px;">
    <div class="container">
        <div class="legal-wrapper" style="max-width: 860px; margin: 0 auto;">
            
            <header class="legal-header" style="margin-bottom: 40px; border-bottom: 1px solid var(--border-color); padding-bottom: 24px;">
                <div class="section-subtitle">TRANSPARENCY &amp; COMPLIANCE</div>
                <h1 style="font-size: 2.4rem; font-weight: 800; margin-top: 8px; margin-bottom: 12px; color: var(--text-primary);">
                    Privacy Policy
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    <strong>Effective Date:</strong> September 18, 2026 &bull; <strong>Last Updated:</strong> September 18, 2026
                </p>
            </header>

            <div class="legal-body" style="color: var(--text-secondary); line-height: 1.8; font-size: 1rem;">
                
                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        1. Introduction &amp; Data Controller
                    </h2>
                    <p style="margin-bottom: 14px;">
                        Welcome to the personal developer portfolio and content management system of <strong>Jairus John D. Valdez</strong> ("I", "me", or "my"), reachable at <a href="mailto:Valdez.jairusjohn.deleste@gmail.com" class="legal-link">Valdez.jairusjohn.deleste@gmail.com</a>.
                    </p>
                    <p>
                        I value your privacy and believe in minimal, transparent data practices. This Privacy Policy informs you of how your personal information is collected, processed, and safeguarded when you visit this website, interact with project demonstrations, or communicate through the contact inquiry form.
                    </p>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        2. Information Collected
                    </h2>
                    <p style="margin-bottom: 12px;">This website collects information under the following limited circumstances:</p>
                    <ul style="margin-left: 24px; margin-bottom: 14px; list-style-type: disc;">
                        <li style="margin-bottom: 8px;">
                            <strong>Direct Communications (Contact Inquiries):</strong> When you submit a message via the <a href="contact.php" class="legal-link">Contact Form</a>, I collect your full name, email address, message subject, and inquiry details.
                        </li>
                        <li style="margin-bottom: 8px;">
                            <strong>Technical &amp; Security Logs:</strong> For server defense and anti-spam abuse detection (OWASP compliance), incoming HTTP requests log the IP address, browser user-agent, and submission timestamp.
                        </li>
                        <li style="margin-bottom: 8px;">
                            <strong>Essential Session Data:</strong> Technical cookies (such as <code>PHPSESSID</code> and cryptographic <code>csrf_token</code>) required to secure form submissions and maintain administrative login states.
                        </li>
                        <li>
                            <strong>Optional Analytics (Strictly Opt-In):</strong> Only if you give explicit consent through our Cookie Preference Manager, anonymous usage telemetry may be processed to measure aggregate page visits.
                        </li>
                    </ul>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        3. Legal Basis for Processing (GDPR Article 6)
                    </h2>
                    <ul style="margin-left: 24px; list-style-type: disc;">
                        <li style="margin-bottom: 8px;">
                            <strong>Consent (Art. 6(1)(a)):</strong> When you voluntarily submit the contact form with the required consent checkbox checked, or when you opt into analytics cookies.
                        </li>
                        <li style="margin-bottom: 8px;">
                            <strong>Legitimate Interests (Art. 6(1)(f)):</strong> To maintain website availability, mitigate automated bot spam via honeypots, protect server infrastructure against denial-of-service, and respond directly to professional employment and project inquiries.
                        </li>
                    </ul>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        4. Data Retention &amp; Security Measures
                    </h2>
                    <p style="margin-bottom: 14px;">
                        I implement defensive cybersecurity standards to safeguard your information:
                    </p>
                    <ul style="margin-left: 24px; margin-bottom: 14px; list-style-type: disc;">
                        <li style="margin-bottom: 8px;"><strong>100% Prepared Statements (PDO):</strong> Eliminates SQL injection vulnerabilities during all database interactions.</li>
                        <li style="margin-bottom: 8px;"><strong>Transport Layer Security (TLS/HTTPS):</strong> All network traffic in transit between your browser and the server is encrypted.</li>
                        <li style="margin-bottom: 8px;"><strong>Strict Access Restrictions:</strong> Administrative endpoints are guarded behind session authentication with modern bcrypt password hashing.</li>
                        <li><strong>Retention Horizon:</strong> Contact inquiries are maintained only for the duration needed to communicate and address your request, after which messages can be permanently purged from the control center.</li>
                    </ul>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        5. Your Rights as a Data Subject
                    </h2>
                    <p style="margin-bottom: 14px;">
                        Depending on your jurisdiction (such as under the EU GDPR or the Philippine Data Privacy Act of 2012), you have the following rights:
                    </p>
                    <ul style="margin-left: 24px; margin-bottom: 14px; list-style-type: disc;">
                        <li style="margin-bottom: 8px;"><strong>Right to Access:</strong> Request a copy of any personal data stored about you.</li>
                        <li style="margin-bottom: 8px;"><strong>Right to Rectification:</strong> Request correction of inaccurate or incomplete information.</li>
                        <li style="margin-bottom: 8px;"><strong>Right to Erasure ("Right to be Forgotten"):</strong> Request that your contact messages and associated records be deleted.</li>
                        <li style="margin-bottom: 8px;"><strong>Right to Restrict or Object:</strong> Restrict or object to further processing of your information.</li>
                        <li><strong>Right to Withdraw Consent:</strong> Withdraw cookie or communication consent at any time without impacting prior lawful processing.</li>
                    </ul>
                    <p>
                        To exercise any of these rights, email me directly at <a href="mailto:Valdez.jairusjohn.deleste@gmail.com" class="legal-link">Valdez.jairusjohn.deleste@gmail.com</a>. Requests will be addressed promptly within 30 days.
                    </p>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        6. Third-Party Services &amp; Cloud Infrastructure
                    </h2>
                    <p style="margin-bottom: 14px;">
                        This website utilizes reputable, privacy-conscious cloud hosting providers:
                    </p>
                    <ul style="margin-left: 24px; list-style-type: disc;">
                        <li style="margin-bottom: 8px;"><strong>Vercel Inc.:</strong> Cloud serverless edge hosting and deployment.</li>
                        <li style="margin-bottom: 8px;"><strong>Supabase Pte. Ltd.:</strong> Managed cloud PostgreSQL database services with encrypted storage.</li>
                        <li><strong>GitHub:</strong> Hosting of open-source project repositories linked throughout the site.</li>
                    </ul>
                </section>

                <section class="legal-section" style="margin-bottom: 36px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        7. Cookie Preferences Management
                    </h2>
                    <p>
                        You can adjust your cookie preferences at any time by clicking the <button type="button" class="btn-link-inline js-cookie-settings-trigger" style="color: var(--accent-cyan); background: none; border: none; font-size: inherit; text-decoration: underline; cursor: pointer; padding: 0;">Cookie Preferences</button> button in our footer or visiting our dedicated <a href="cookies.php" class="legal-link">Cookie Policy</a>.
                    </p>
                </section>

                <section class="legal-section" style="border-top: 1px solid var(--border-color); padding-top: 24px;">
                    <h2 style="color: var(--text-primary); font-size: 1.4rem; margin-bottom: 14px; font-weight: 700;">
                        8. Contact Information
                    </h2>
                    <p>
                        For questions, privacy requests, or feedback regarding this policy, please contact:<br>
                        <strong>Jairus John D. Valdez</strong><br>
                        Quezon City University &bull; Caloocan City, Philippines<br>
                        Email: <a href="mailto:Valdez.jairusjohn.deleste@gmail.com" class="legal-link">Valdez.jairusjohn.deleste@gmail.com</a>
                    </p>
                </section>

            </div>

            <div style="margin-top: 40px; display: flex; gap: 14px; flex-wrap: wrap;">
                <a href="terms.php" class="btn btn-outline btn-sm">Read Terms &amp; Conditions &rarr;</a>
                <a href="cookies.php" class="btn btn-outline btn-sm">View Cookie Policy &rarr;</a>
                <a href="contact.php" class="btn btn-primary btn-sm">Contact Jairus &rarr;</a>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
