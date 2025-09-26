/**
 * Module: Fab/Messenger/SendAgainMenuItemNative
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';

class MessengerSendAgain {
    constructor() {
        this.initialized = false;
        this.modal = null;
    }

    /**
     * Get edit storage URL
     */
    getEditStorageUrl(url, type, searchTerm = '') {
        const uri = new URL(url, window.location.origin);

        // Get selected items
        const columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

        const params = new URLSearchParams();
        params.append('tx_messenger_user_messenger[matches][uid]', columnsToSend.join(','));
        params.append('dataType', type);
        params.append('search', searchTerm);

        uri.search = params.toString();
        return uri.toString();
    }

    /**
     * Initialize send again functionality
     */
    initialize() {
        if (this.initialized) return;

        document.addEventListener('click', (e) => {
            const sendAgainButton = e.target.closest('.btn-sendAgain');
            if (sendAgainButton) {
                e.preventDefault();
                this.handleSendAgainClick(sendAgainButton);
            }
        });

        this.initialized = true;
    }

    /**
     * Handle send again button click
     */
    handleSendAgainClick(button) {
        const type = button.dataset.dataType;
        const searchTerm = button.dataset.searchTerm || '';

        const ajaxUrls = window.TYPO3?.settings?.ajaxUrls || {};
        const confirmUrl = ajaxUrls.messenger_send_again_confirmation;
        const sendAgainUrl = ajaxUrls.messenger_send_again;

        if (!confirmUrl || !sendAgainUrl) {
            console.error('Send again URLs not found in TYPO3 settings');
            this.showError('Send again configuration error');
            return;
        }

        const url = this.getEditStorageUrl(confirmUrl, type, searchTerm);

        this.modal = Modal.advanced({
            type: Modal.types.ajax,
            title: 'Send Again',
            severity: top.TYPO3.Severity.notice,
            content: url,
            buttons: [
                {
                    text: 'Cancel',
                    btnClass: 'btn btn-default',
                    trigger: () => {
                        Modal.dismiss();
                    },
                },
                {
                    text: 'Send Again',
                    btnClass: 'btn btn-primary',
                    trigger: () => {
                        this.performSendAgain(sendAgainUrl, type, searchTerm);
                    },
                },
            ],
        });
    }

    /**
     * Perform the actual send again operation
     */
    async performSendAgain(sendAgainUrl, type, searchTerm) {
        // Disable buttons during operation
        const buttons = this.modal.querySelectorAll('.btn');
        buttons.forEach(btn => btn.disabled = true);

        const finalSendAgainUrl = this.getEditStorageUrl(sendAgainUrl, type, searchTerm);

        try {
            const response = await fetch(finalSendAgainUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });

            if (response.ok) {
                const result = await response.text(); // Send again might return plain text
                
                this.showSuccess('Messages sent again successfully');
                Modal.dismiss();
                
                // Reload the page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 500);
                
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            console.error('Send again error:', error);
            this.showError('Send again failed: ' + error.message);
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
const sendAgain = new MessengerSendAgain();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => sendAgain.initialize());
} else {
    sendAgain.initialize();
}

// Make available globally
window.MessengerSendAgain = sendAgain;

export default MessengerSendAgain;
