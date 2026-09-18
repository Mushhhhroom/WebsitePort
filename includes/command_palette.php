<?php
/**
 * Interactive Command Palette & Spotlight Navigation Component
 * Accessible Native HTML5 <dialog> with Keyboard Trap and Fuzzy Filtering
 */
?>
<dialog id="commandPalette" class="cmd-palette-dialog" aria-modal="true" aria-label="Command Palette">
    <div class="cmd-palette-box">
        <div class="cmd-search-bar">
            <span class="cmd-search-icon" aria-hidden="true">⚡</span>
            <input type="text" 
                   id="cmdSearchInput" 
                   class="cmd-input" 
                   placeholder="Type a command, page, or search (e.g. 'projects', 'sync', 'resume')..." 
                   autocomplete="off" 
                   spellcheck="false" 
                   aria-autocomplete="list" 
                   aria-controls="cmdResultsList">
            <button type="button" class="cmd-esc-badge" id="cmdDismissBtn" aria-label="Close Command Menu">ESC</button>
        </div>

        <div class="cmd-results" id="cmdResultsList" role="listbox" tabindex="-1">
            <!-- Group 1: Navigation -->
            <div class="cmd-group-title">Primary Navigation</div>
            
            <a href="index.php" class="cmd-item" role="option" data-keywords="home main landing start hero" data-key="1">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">🏠</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">Home Console</span>
                        <span class="cmd-item-desc">Hero, core metrics, featured systems &amp; overview</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">1</kbd>
            </a>

            <a href="about.php" class="cmd-item" role="option" data-keywords="about resume experience education skills bio cv" data-key="2">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">📄</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">About &amp; Resume</span>
                        <span class="cmd-item-desc">Education at QCU, leadership experience &amp; technical stack</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">2</kbd>
            </a>

            <a href="projects.php" class="cmd-item" role="option" data-keywords="projects gallery portfolio work code apps mobile systems" data-key="3">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">💻</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">Projects Gallery</span>
                        <span class="cmd-item-desc">Browse full database of web, application, and mobile projects</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">3</kbd>
            </a>

            <a href="contact.php" class="cmd-item" role="option" data-keywords="contact message email hire collaborate reach" data-key="4">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">✉️</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">Contact &amp; Inquiry</span>
                        <span class="cmd-item-desc">Send a direct message or collaborative inquiry</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">4</kbd>
            </a>

            <?php if (is_logged_in()): ?>
                <a href="dashboard.php" class="cmd-item" role="option" data-keywords="admin dashboard crud manage database sync" data-key="5">
                    <div class="cmd-item-left">
                        <span class="cmd-item-icon">⚙️</span>
                        <div class="cmd-item-details">
                            <span class="cmd-item-title">Admin Dashboard</span>
                            <span class="cmd-item-desc">Manage project items, resume sections &amp; read incoming messages</span>
                        </div>
                    </div>
                    <kbd class="cmd-item-key">5</kbd>
                </a>
            <?php else: ?>
                <a href="login.php" class="cmd-item" role="option" data-keywords="login admin authenticate portal sign in" data-key="5">
                    <div class="cmd-item-left">
                        <span class="cmd-item-icon">🔒</span>
                        <div class="cmd-item-details">
                            <span class="cmd-item-title">Admin Portal</span>
                            <span class="cmd-item-desc">Secure administrative authentication and CRUD control</span>
                        </div>
                    </div>
                    <kbd class="cmd-item-key">5</kbd>
                </a>
            <?php endif; ?>

            <!-- Group 2: System Telemetry & Utilities -->
            <div class="cmd-group-title">System &amp; Telemetry</div>

            <button type="button" class="cmd-item js-cmd-action" role="option" data-action="open-telemetry" data-keywords="telemetry architecture system specs database stack vercel" data-key="T">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">⚡</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">System Telemetry Console</span>
                        <span class="cmd-item-desc">Inspect hybrid database sync, HMAC-SHA256 &amp; edge topology</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">T</kbd>
            </button>

            <a href="sync_db.php" class="cmd-item" role="option" data-keywords="sync database supabase mysql postgres cluster center" data-key="S">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">🔄</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">Database Sync Center</span>
                        <span class="cmd-item-desc">Dual sync bridge between Supabase Cloud and Local MySQL</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">S</kbd>
            </a>

            <button type="button" class="cmd-item js-cmd-action" role="option" data-action="open-cookies" data-keywords="cookie privacy gdpr consent preferences compliance tracking" data-key="C">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">🛡️</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">Cookie &amp; Privacy Preferences</span>
                        <span class="cmd-item-desc">Granular opt-in toggles for Necessary, Analytics &amp; Marketing</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">C</kbd>
            </button>

            <button type="button" class="cmd-item js-cmd-action" role="option" data-action="toggle-audio" data-keywords="audio sound haptics click synth feedback mute unmute" data-key="A">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon js-audio-icon">🔇</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title js-audio-label">Toggle Audio Haptics (Currently Off)</span>
                        <span class="cmd-item-desc">Web Audio API synthesized micro-clicks and kinetic feedback</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">A</kbd>
            </button>

            <!-- Group 3: Quick Actions & External -->
            <div class="cmd-group-title">Quick Actions &amp; Network</div>

            <button type="button" class="cmd-item js-cmd-action" role="option" data-action="copy-email" data-keywords="copy email address contact valdez mailto" data-key="E">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">📋</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">Copy Contact Email</span>
                        <span class="cmd-item-desc">Valdez.jairusjohn.deleste@gmail.com</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">E</kbd>
            </button>

            <a href="https://github.com/Mushhhhroom" target="_blank" rel="noopener noreferrer" class="cmd-item" role="option" data-keywords="github code repos open source git mushhhhroom" data-key="G">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">💻</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">GitHub Profile (Mushhhhroom)</span>
                        <span class="cmd-item-desc">Explore public repositories, commits and contributions</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">G</kbd>
            </a>

            <a href="https://www.linkedin.com/in/jairus-valdez-19469a313/" target="_blank" rel="noopener noreferrer" class="cmd-item" role="option" data-keywords="linkedin profile social career network connect" data-key="L">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">💼</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">LinkedIn Profile</span>
                        <span class="cmd-item-desc">Professional network and technical portfolio profile</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">L</kbd>
            </a>

            <button type="button" class="cmd-item js-cmd-action" role="option" data-action="scroll-top" data-keywords="top scroll up header start page" data-key="↑">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">🔝</span>
                    <div class="cmd-item-details">
                        <span class="cmd-item-title">Scroll to Top</span>
                        <span class="cmd-item-desc">Return viewport smoothly to the page summit</span>
                    </div>
                </div>
                <kbd class="cmd-item-key">↑</kbd>
            </button>
        </div>

        <div class="cmd-empty-state" id="cmdEmptyState" style="display: none;">
            <span>No matching commands or destinations found.</span>
        </div>

        <div class="cmd-palette-footer">
            <span class="cmd-footer-tip"><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
            <span class="cmd-footer-tip"><kbd>↵</kbd> Select</span>
            <span class="cmd-footer-tip"><kbd>ESC</kbd> Exit</span>
            <span class="cmd-footer-tip"><kbd>Ctrl+K</kbd> / <kbd>⌘K</kbd> Toggle</span>
        </div>
    </div>
</dialog>
