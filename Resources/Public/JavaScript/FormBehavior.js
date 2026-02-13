/**
 * Backend form behavior: toggles and form submit without inline event handlers (CSP-compliant).
 */
(function() {
    'use strict';

    function init() {
        console.log('FormBehavior.js: Initializing event delegation on document.body');
        document.body.addEventListener('change', function(e) {
            var target = e.target;
            if (!target || !target.id) return;

            console.log('FormBehavior.js: Change event on element:', target.id);

            if (target.id === 'has-body-text') {
                console.log('FormBehavior.js: Toggling message-body-container');
                var container = document.getElementById('message-body-container');
                if (container) {
                    container.style.display = target.checked ? 'block' : 'none';
                    console.log('FormBehavior.js: message-body-container display set to', container.style.display);
                } else {
                    console.error('FormBehavior.js: message-body-container not found');
                }
                return;
            }

            if (target.id === 'has-body-test') {
                console.log('FormBehavior.js: Toggling recipient-test');
                var recipientTest = document.getElementById('recipient-test');
                if (recipientTest) {
                    recipientTest.style.display = target.checked ? 'block' : 'none';
                    target.value = target.checked ? '1' : '0';
                    console.log('FormBehavior.js: recipient-test display set to', recipientTest.style.display);
                } else {
                    console.error('FormBehavior.js: recipient-test not found');
                }
                return;
            }

            if (target.id === 'itemsPerPage' && target.form) {
                console.log('FormBehavior.js: Submitting form for itemsPerPage');
                target.form.submit();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
