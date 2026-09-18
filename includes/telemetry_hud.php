<?php
/**
 * Interactive Telemetry HUD Dock & Scroll Spy Navigation Component
 * Fixed cockpit navigation with scroll depth, section spy & audio controls
 */
?>
<!-- Floating Interactive Telemetry Navigation HUD -->
<aside class="telemetry-hud" id="telemetryHud" aria-label="Reading & Navigation Telemetry Dock">
    <div class="hud-pill">
        <span class="hud-section-label" id="hudSectionLabel">LOC // HERO</span>
        <span class="hud-progress-val" id="hudProgressVal">0%</span>
    </div>

    <!-- Quick Command Palette Launcher -->
    <button type="button" 
            class="hud-action-btn js-cmd-palette-trigger" 
            id="hudCmdBtn" 
            title="Open Command Palette (Ctrl+K)" 
            aria-label="Open Command Menu (Ctrl+K)">
        <span aria-hidden="true">⌘K</span>
    </button>

    <!-- Web Audio API Synthesizer Toggle -->
    <button type="button" 
            class="hud-action-btn js-audio-toggle-btn" 
            id="hudAudioToggleBtn" 
            title="Toggle UI Audio Feedback" 
            aria-label="Toggle UI Audio Feedback">
        <span class="js-audio-icon" aria-hidden="true">🔇</span>
    </button>

    <!-- Back to Top Kinetic Launcher -->
    <button type="button" 
            class="hud-action-btn hud-scroll-top-btn" 
            id="hudScrollTopBtn" 
            title="Scroll to Top" 
            aria-label="Scroll back to top">
        <span aria-hidden="true">↑</span>
    </button>
</aside>

<!-- Global HUD Feedback Toast -->
<div class="hud-toast" id="hudToast" role="status" aria-live="polite">
    <span class="hud-toast-icon" id="hudToastIcon">✓</span>
    <span class="hud-toast-msg" id="hudToastMsg">Command Executed</span>
</div>
