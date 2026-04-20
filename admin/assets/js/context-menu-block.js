/**
 * Disable browser context menu (right-click) for the entire document.
 */
(function() {
    'use strict';

    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
    }, true);
})();
