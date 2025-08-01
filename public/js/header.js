// public/js/header.js

document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const closeMobileMenuButton = document.getElementById('close-mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu && closeMobileMenuButton) {
        mobileMenuButton.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent this click from immediately closing the menu
            mobileMenu.classList.add('active'); // Add 'active' class to show menu
        });

        closeMobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.remove('active'); // Remove 'active' class to hide menu
        });

        // Close menu when clicking outside
        document.body.addEventListener('click', function(event) {
            // Check if the clicked element is NOT the menu itself and NOT the button that opens it
            // and if the menu is currently active.
            if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target) && mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
            }
        });
    }
});