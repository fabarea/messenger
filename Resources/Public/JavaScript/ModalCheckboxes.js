/**
 * Module: Fab/Messenger/ModalCheckboxes
 * Handles checkbox interactions in the modal - initializes immediately when loaded
 */

(function() {
    'use strict';

    function initCheckboxes() {
        // Handle "Replace message body" checkbox
        const hasBodyTextCheckbox = document.getElementById('has-body-text');
        if (hasBodyTextCheckbox) {
            hasBodyTextCheckbox.addEventListener('change', function() {
                const container = document.getElementById('message-body-container');
                if (container) {
                    container.style.display = this.checked ? 'block' : 'none';
                }
            });
        }

        // Handle "Send test" checkbox
        const hasBodyTestCheckbox = document.getElementById('has-body-test');
        if (hasBodyTestCheckbox) {
            hasBodyTestCheckbox.addEventListener('change', function() {
                const recipientTest = document.getElementById('recipient-test');
                if (recipientTest) {
                    recipientTest.style.display = this.checked ? 'block' : 'none';
                    this.value = this.checked ? '1' : '0';
                }
            });
        }
    }

    // Initialize immediately - this script is loaded when the modal content is rendered
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCheckboxes);
    } else {
        // DOM is already ready, init now
        initCheckboxes();
    }

    // Also try after a short delay in case of async loading
    setTimeout(initCheckboxes, 100);
})();

export default {};
