/**
 * Backend form behavior: toggles and form submit without inline event handlers (CSP-compliant).
 */
(function() {
    'use strict';

    function init() {
        document.body.addEventListener('change', function(e) {
            var target = e.target;
            if (!target || !target.id) return;

            if (target.id === 'has-body-text') {
                var container = document.getElementById('message-body-container');
                if (container) container.style.display = target.checked ? 'block' : 'none';
                return;
            }

            if (target.id === 'has-body-test') {
                var recipientTest = document.getElementById('recipient-test');
                if (recipientTest) {
                    recipientTest.style.display = target.checked ? 'block' : 'none';
                    target.value = target.checked ? '1' : '0';
                }
                return;
            }

            if (target.id === 'itemsPerPage' && target.form) {
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
