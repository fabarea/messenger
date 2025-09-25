/**
 * Module: Fab/Messenger/UpdateRecipient
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';

class MessengerUpdateRecipient {
    constructor() {
        this.initialized = false;
        this.modal = null;
    }

    /**
     * Get edit recipient URL
     */
    getEditRecipientUrl(url, data = [], searchTerm = '') {
        const uri = new URL(url, window.location.origin);

        // Get selected items
        const columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

        const params = new URLSearchParams();
        params.append('tx_messenger_user_messengerm5[matches][uid]', columnsToSend.join(','));
        params.append('data', data);
        params.append('search', searchTerm);

        uri.search = params.toString();
        return uri.toString();
    }

    /**
     * Initialize update recipient functionality
     */
    initialize() {
        if (this.initialized) return;

        document.addEventListener('click', (e) => {
            const updateButton = e.target.closest('.btn-update-recipient');
            if (updateButton) {
                e.preventDefault();
                this.handleUpdateRecipientClick(updateButton);
            }
        });

        this.initialized = true;
    }

    /**
     * Handle update recipient button click
     */
    handleUpdateRecipientClick(button) {
        const searchTerm = button.dataset.searchTerm || '';

        const ajaxUrls = window.TYPO3?.settings?.ajaxUrls || {};
        const updateUrl = ajaxUrls.newsletter_update_recipient;
        const saveUrl = ajaxUrls.newsletter_update_recipient_save;

        if (!updateUrl || !saveUrl) {
            console.error('Update recipient URLs not found in TYPO3 settings');
            this.showError('Update recipient configuration error');
            return;
        }

        const url = this.getEditRecipientUrl(updateUrl, [], searchTerm);

        this.modal = Modal.advanced({
            type: Modal.types.ajax,
            title: 'Update recipient',
            severity: top.TYPO3.Severity.notice,
            content: url,
            staticBackdrop: false,
            buttons: [
                {
                    text: 'Cancel',
                    btnClass: 'btn btn-default',
                    trigger: () => {
                        Modal.dismiss();
                    },
                },
                {
                    text: 'Update recipient',
                    btnClass: 'btn btn-primary',
                    trigger: () => {
                        this.performUpdateRecipient(saveUrl, searchTerm);
                    },
                },
            ],
        });
    }

    /**
     * Perform the actual update recipient operation
     */
    async performUpdateRecipient(saveUrl, searchTerm) {
        // Disable buttons during operation
        const buttons = this.modal.querySelectorAll('.btn');
        buttons.forEach(btn => btn.disabled = true);

        const form = window.parent.document.querySelector('#form-update-many-recipients');
        if (!form) {
            this.showError('Update form not found');
            return;
        }

        const finalSaveUrl = this.getEditRecipientUrl(saveUrl, [], searchTerm);

        try {
            const formData = new FormData(form);
            const response = await fetch(finalSaveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (response.ok) {
                const result = await response.text(); // Update might return plain text
                
                this.showSuccess('Recipients updated successfully');
                Modal.dismiss();
                
                // Refresh the page to show updated recipients
                setTimeout(() => {
                    window.location.reload();
                }, 500);
                
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            console.error('Update recipient error:', error);
            this.showError('Update recipient failed: ' + error.message);
        } finally {
            // Re-enable buttons
            buttons.forEach(btn => btn.disabled = false);
        }
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        if (Notification) {
            Notification.success('Success', message);
        } else {
            console.log('Success: ' + message);
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        if (Notification) {
            Notification.error('Error', message);
        } else {
            alert('Error: ' + message);
        }
    }
}

// Initialize when DOM is ready
const updateRecipient = new MessengerUpdateRecipient();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => updateRecipient.initialize());
} else {
    updateRecipient.initialize();
}

// Make available globally
updateRecipient.initialized = true;
window.MessengerUpdateRecipient = updateRecipient;

export default MessengerUpdateRecipient;
