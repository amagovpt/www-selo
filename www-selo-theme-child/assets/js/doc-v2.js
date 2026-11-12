/**
 * Documentation V2 Sidebar Interaction
 * Handles active state for duplicate links and anchor navigation.
 */
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.doc-sidebar-nav a');
    const cookieName = 'doc_v2_active_id';

    // Helper to set cookie
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    // Function to set active class
    function setActiveItem(targetLink) {
        // Remove active class and aria-current from all items
        document.querySelectorAll('.doc-sidebar-nav .current-menu-item').forEach(item => {
            item.classList.remove('current-menu-item');
            const link = item.querySelector('a');
            if (link) link.removeAttribute('aria-current');
        });

        // Add active class to the clicked item's parent li
        const parentLi = targetLink.closest('li');
        if (parentLi) {
            parentLi.classList.add('current-menu-item');
            targetLink.setAttribute('aria-current', 'page');
            
            // Extract menu item ID from class (e.g., menu-item-123)
            const classes = Array.from(parentLi.classList);
            const idClass = classes.find(c => c.startsWith('menu-item-'));
            
            if (idClass) {
                const id = idClass.replace('menu-item-', '');
                // Save to cookie for server-side reading
                setCookie(cookieName, id, 1); // 1 day expiration
            }
        }
    }

    // 1. Handle clicks
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            setActiveItem(this);
        });
    });

    // 2. Handle initial load with Hash (for anchor links)
    if (window.location.hash) {
        const hash = window.location.hash;
        const activeLink = Array.from(sidebarLinks).find(link => {
            return link.href.endsWith(hash);
        });

        if (activeLink) {
            setActiveItem(activeLink);
        }
    }

    // 3. Handle manual collapse toggle
    const collapseButton = document.getElementById('toggleMenuBtn');
    const sidebarNav = document.getElementById('docSidebarNav');
    
    if (collapseButton && sidebarNav) {
        // Get the icon element
        const iconElement = collapseButton.querySelector('i[class*="AMA-Setacurtaoficial"]');
        
        // Manual toggle
        collapseButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const isHidden = sidebarNav.classList.contains('hide');
            const buttonText = collapseButton.querySelector('.btn-text');
            
            if (isHidden) {
                // Show - Menu aberto: seta para esquerda
                sidebarNav.classList.remove('hide');
                if (buttonText) buttonText.textContent = 'Menu';
                if (iconElement) {
                    iconElement.className = 'AMA-SetacurtaoficialEsq-Line';
                }
                collapseButton.setAttribute('aria-expanded', 'true');
                collapseButton.setAttribute('aria-label', 'Ocultar menu de navegação lateral');
            } else {
                // Hide - Menu fechado: seta para direita
                sidebarNav.classList.add('hide');
                if (buttonText) buttonText.textContent = 'Menu';
                if (iconElement) {
                    iconElement.className = 'AMA-SetacurtaoficialDto-Line';
                }
                collapseButton.setAttribute('aria-expanded', 'false');
                collapseButton.setAttribute('aria-label', 'Mostrar menu de navegação lateral');
            }
        });
        
        // Set initial icon state based on current visibility
        if (sidebarNav.classList.contains('hide')) {
            if (iconElement) {
                iconElement.className = 'AMA-SetacurtaoficialDto-Line';
            }
        } else {
            if (iconElement) {
                iconElement.className = 'AMA-SetacurtaoficialEsq-Line';
            }
        }
    }

    // 4. Handle filter checkboxes for content type filtering
    const filterCheckboxes = document.querySelectorAll('.doc-filter-checkbox');
    
    // Function to filter menu items and content based on selected types
    function applyFilters() {
        const sitiosWebChecked = document.getElementById('filter-sitios-web').checked;
        const aplicacoesMoveisChecked = document.getElementById('filter-aplicacoes-moveis').checked;
        
        // Helper function to extract ID from href
        function extractIdFromHref(href) {
            if (!href) return null;
            if (href.includes('#')) {
                return href.split('#')[1];
            } else {
                // Remove protocol and domain, keep path
                return href.replace(/^https?:\/\/[^\/]+/, '').replace(/^\//, '').replace(/\/$/, '');
            }
        }
        
        // Filter menu items and content sections
        const menuItems = document.querySelectorAll('.doc-sidebar-nav li[data-content-type]');
        menuItems.forEach(item => {
            const contentType = item.getAttribute('data-content-type');
            const shouldShow = (contentType === 'sitios-web' && sitiosWebChecked) || 
                             (contentType === 'aplicacoes-moveis' && aplicacoesMoveisChecked);
            
            // Show/hide menu item
            if (shouldShow) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
            
            // Find the link in this menu item
            const link = item.querySelector('a[href]');
            if (link) {
                const href = link.getAttribute('href');
                const targetId = extractIdFromHref(href);
                
                if (targetId) {
                    // Try to find the content element by ID
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        // Show/hide content section
                        if (shouldShow) {
                            targetElement.style.display = '';
                        } else {
                            targetElement.style.display = 'none';
                        }
                    }
                }
            }
        });
        
        // Also handle menu items without data-content-type (they should always be visible)
        const itemsWithoutType = document.querySelectorAll('.doc-sidebar-nav li:not([data-content-type])');
        itemsWithoutType.forEach(item => {
            item.style.display = '';
            
            // Also show their corresponding content
            const link = item.querySelector('a[href]');
            if (link) {
                const href = link.getAttribute('href');
                const targetId = extractIdFromHref(href);
                if (targetId) {
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.style.display = '';
                    }
                }
            }
        });
        
        // Save filter state to localStorage
        localStorage.setItem('doc-filter-sitios-web', sitiosWebChecked ? 'checked' : 'unchecked');
        localStorage.setItem('doc-filter-aplicacoes-moveis', aplicacoesMoveisChecked ? 'checked' : 'unchecked');
    }
    
    // Load saved filter state
    const sitiosWebState = localStorage.getItem('doc-filter-sitios-web');
    const aplicacoesMoveisState = localStorage.getItem('doc-filter-aplicacoes-moveis');
    
    if (sitiosWebState === 'unchecked') {
        document.getElementById('filter-sitios-web').checked = false;
    }
    if (aplicacoesMoveisState === 'unchecked') {
        document.getElementById('filter-aplicacoes-moveis').checked = false;
    }
    
    // Apply filters on page load
    setTimeout(() => applyFilters(), 100);
    
    // Handle filter checkbox changes
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            applyFilters();
        });
    });
});

