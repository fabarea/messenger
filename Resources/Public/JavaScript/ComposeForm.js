/**
 * Module: Fab/Messenger/ComposeForm
 * Handles form interactions for the Compose view
 */

const ComposeForm = {
    initialized: false,

    initialize: function() {
        if (this.initialized) {
            return;
        }
        this.initialized = true;

        this.initializeBodyTextCheckbox();
        this.initializeItemsPerPageSelect();
    },

    /**
     * Initialize the "has-body-text" checkbox to show/hide message body container
     */
    initializeBodyTextCheckbox: function() {
        const checkbox = document.getElementById('has-body-text');
        if (checkbox && !checkbox.dataset.listenerAttached) {
            checkbox.dataset.listenerAttached = 'true';
            checkbox.addEventListener('change', function() {
                const container = document.getElementById('message-body-container');
                if (container) {
                    container.style.display = this.checked ? 'block' : 'none';
                }
            });
        }
    },

    /**
     * Initialize items per page select to auto-submit form on change
     */
    initializeItemsPerPageSelect: function() {
        const select = document.querySelector('.items-per-page-select');
        if (select && !select.dataset.listenerAttached) {
            select.dataset.listenerAttached = 'true';
            select.addEventListener('change', function() {
                if (this.form) {
                    this.form.submit();
                }
            });
        }
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        ComposeForm.initialize();
    });
} else {
    ComposeForm.initialize();
}

export default ComposeForm;
