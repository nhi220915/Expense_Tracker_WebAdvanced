// Fix navigation - ensure tab buttons work despite any JavaScript errors
export function initNavigation() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupNavigation);
    } else {
        setupNavigation();
    }
}

function setupNavigation() {
    // Get all main tab buttons (they are <a> tags with href)
    const tabButtons = document.querySelectorAll('.main-tab-button');

    if (tabButtons.length === 0) {
        // Retry after a short delay if buttons not found yet
        setTimeout(setupNavigation, 100);
        return;
    }

    tabButtons.forEach(button => {
        // Remove any existing click listeners by cloning and replacing the element
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        // Add our own click handler with highest priority
        newButton.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            if (href && (href.startsWith('/') || href.startsWith('http'))) {
                // Stop any other handlers from interfering
                e.stopImmediatePropagation();

                // Force navigation using window.location
                console.log('Navigation fix: Navigating to', href);
                window.location.href = href;

                // Prevent default just in case
                e.preventDefault();
                return false;
            }
        }, true); // Capture phase - runs before other listeners
    });

    console.log('✅ Navigation fix initialized for', tabButtons.length, 'tab buttons');
}

// Auto-initialize
initNavigation();
