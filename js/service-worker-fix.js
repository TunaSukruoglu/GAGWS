/**
 * Service Worker Fix
 * This script prevents service worker registration errors
 */
(function() {
    // Check if service worker is in navigator and block any automatic registration
    if ('serviceWorker' in navigator) {
        // Wrap the original register method to prevent errors
        const originalRegister = navigator.serviceWorker.register;
        
        // Override the register method to prevent registration
        navigator.serviceWorker.register = function() {
            console.log('Service Worker registration prevented');
            return Promise.resolve(null); // Return a resolved promise to avoid errors
        };
        
        // If there is already a controller, let it know we don't want to use it
        if (navigator.serviceWorker.controller) {
            try {
                navigator.serviceWorker.controller.postMessage('SKIP_WAITING');
            } catch (e) {
                console.warn('Failed to send message to service worker:', e);
            }
        }
        
        // Also handle any errors with unregistration attempts
        window.addEventListener('error', function(e) {
            if (e.message && (
                e.message.toLowerCase().includes('serviceworker') || 
                e.message.toLowerCase().includes('service worker')
            )) {
                console.warn('Service Worker error intercepted:', e.message);
                e.preventDefault();
                return true; // Prevent the error from bubbling up
            }
        });
        
        // Handle Promise rejections
        window.addEventListener('unhandledrejection', function(e) {
            if (e.reason && (
                e.reason.toString().toLowerCase().includes('serviceworker') ||
                e.reason.toString().toLowerCase().includes('service worker')
            )) {
                console.warn('Service Worker promise rejection intercepted:', e.reason);
                e.preventDefault();
                return true; // Prevent the rejection from bubbling up
            }
        });
    }
})();
