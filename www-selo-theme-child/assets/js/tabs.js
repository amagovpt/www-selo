/**
 * Accessible Tabs Implementation
 * Supports multiple tablists on the same page
 */
(function() {
    'use strict';

    // Wait for DOM to be ready
    function initTabs() {
        // Find all tablists on the page
        const tablists = document.querySelectorAll('[role="tablist"]');

        tablists.forEach(function(tablist) {
            // Get tabs and panels for this specific tablist
            const tabs = tablist.querySelectorAll('[role="tab"]');
            const tabPanels = [];

            // Find corresponding panels for this tablist
            tabs.forEach(function(tab) {
                const panelId = tab.getAttribute('aria-controls');
                if (panelId) {
                    const panel = document.getElementById(panelId);
                    if (panel) {
                        tabPanels.push(panel);
                    }
                }
            });

            // Function to switch tabs
            function switchTab(newTab) {
                const newTabId = newTab.getAttribute('id');
                const newPanelId = newTab.getAttribute('aria-controls');
                const newPanel = document.getElementById(newPanelId);

                // Deactivate all tabs in this tablist
                tabs.forEach(function(tab) {
                    tab.setAttribute('aria-selected', 'false');
                    tab.setAttribute('tabindex', '-1');
                    tab.classList.remove('active');
                });

                // Hide all panels for this tablist
                tabPanels.forEach(function(panel) {
                    panel.setAttribute('hidden', '');
                });

                // Activate new tab
                newTab.setAttribute('aria-selected', 'true');
                newTab.setAttribute('tabindex', '0');
                newTab.classList.add('active');

                // Show new panel
                if (newPanel) {
                    newPanel.removeAttribute('hidden');
                }
            }

            function panelHasFormidableError(panel) {
                const hasFormidableForm = panel.querySelector('.frm_forms, .frm-show-form, form[id^="form_"]');
                const hasFormidableError = panel.querySelector('.frm_error_style[role="alert"]');

                return hasFormidableForm && hasFormidableError;
            }

            // Click event for tabs
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    switchTab(this);
                });

                // Keyboard navigation
                tab.addEventListener('keydown', function(e) {
                    let targetTab = null;
                    const tabsArray = Array.from(tabs);
                    const currentIndex = tabsArray.indexOf(this);

                    // Arrow keys navigation
                    if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                        if (e.key === 'ArrowRight') {
                            // Move to next tab, wrap to first if at end
                            targetTab = tabsArray[(currentIndex + 1) % tabsArray.length];
                        } else {
                            // Move to previous tab, wrap to last if at start
                            targetTab = tabsArray[(currentIndex - 1 + tabsArray.length) % tabsArray.length];
                        }

                        e.preventDefault();
                        switchTab(targetTab);
                    }

                    // Home/End keys
                    if (e.key === 'Home') {
                        e.preventDefault();
                        switchTab(tabsArray[0]);
                    }
                    if (e.key === 'End') {
                        e.preventDefault();
                        switchTab(tabsArray[tabsArray.length - 1]);
                    }
                });
            });

            // Initialize: ensure first active tab is properly set
            const activeTab = tablist.querySelector('[role="tab"][aria-selected="true"]');
            if (activeTab) {
                const activePanelId = activeTab.getAttribute('aria-controls');
                const activePanel = document.getElementById(activePanelId);
                
                // Hide all panels first
                tabPanels.forEach(function(panel) {
                    panel.setAttribute('hidden', '');
                });
                
                // Show active panel
                if (activePanel) {
                    activePanel.removeAttribute('hidden');
                }
            }

            const tabWithFormidableError = Array.from(tabs).find(function(tab) {
                const panelId = tab.getAttribute('aria-controls');
                const panel = panelId ? document.getElementById(panelId) : null;

                return panel && panelHasFormidableError(panel);
            });

            if (tabWithFormidableError) {
                switchTab(tabWithFormidableError);
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTabs);
    } else {
        initTabs();
    }
})();
