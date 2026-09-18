<?php
/**
 * System Telemetry & Quick Action Dialog Component
 * Native HTML5 <dialog> with Accessible Keyboard Traps and Zero CLS
 */
?>
<dialog id="systemModal" class="system-dialog" aria-labelledby="modalSystemHeading" aria-modal="true">
    <div class="dialog-frame">
        <div class="dialog-header">
            <div class="dialog-header-left">
                <span class="dialog-pip" aria-hidden="true"></span>
                <h3 id="modalSystemHeading" class="dialog-title">SYSTEM TELEMETRY // CORE NODE</h3>
            </div>
            <button type="button" class="dialog-close-btn" id="closeSystemModalBtn" aria-label="Close system telemetry window">&times;</button>
        </div>

        <div class="dialog-body">
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 16px; font-family: var(--font-mono);">
                RUNTIME ARCHITECTURE &bull; SWISS PRECISION OBSIDIAN STACK
            </p>

            <div class="dialog-telemetry-row">
                <span style="color: var(--text-muted);">Database Layer:</span>
                <span style="color: var(--accent-cyan); font-weight: 600;">Supabase PG &bull; Local MySQL</span>
            </div>

            <div class="dialog-telemetry-row">
                <span style="color: var(--text-muted);">Auth Protocol:</span>
                <span style="color: var(--accent-amber); font-weight: 600;">Stateless HMAC-SHA256</span>
            </div>

            <div class="dialog-telemetry-row">
                <span style="color: var(--text-muted);">Compliance:</span>
                <span style="color: var(--accent-emerald); font-weight: 600;">WCAG 2.1 AA &bull; GDPR</span>
            </div>

            <div class="dialog-telemetry-row">
                <span style="color: var(--text-muted);">Edge Network:</span>
                <span style="color: var(--text-primary); font-weight: 600;">Vercel Serverless (iad1)</span>
            </div>

            <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 14px; line-height: 1.5;">
                Engineered with parameterization, CSRF double-cookie tokens, XSS escaping, and responsive accessible primitives.
            </p>
        </div>

        <div class="dialog-footer">
            <a href="sync_db.php" class="btn btn-outline btn-sm">
                <span>🔄 DB Sync Center</span>
            </a>
            <button type="button" class="btn btn-primary btn-sm" id="dismissSystemModalBtn">
                <span>Dismiss [ESC]</span>
            </button>
        </div>
    </div>
</dialog>
