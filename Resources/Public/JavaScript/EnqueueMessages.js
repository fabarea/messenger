/**
 * Module: Fab/Messenger/EnqueueMessages
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';

class MessengerEnqueueMessages {
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
     * Initialize enqueue messages functionality
     */
    initialize() {
        if (this.initialized) return;

        document.addEventListener('click', (e) => {
            const sendButton = e.target.closest('.btn-send-message');
            if (sendButton) {
                e.preventDefault();
                this.handleSendMessageClick(sendButton);
            }
        });

        this.initialized = true;
    }

    /**
     * Handle send message button click
     */
    handleSendMessageClick(button) {
        const searchTerm = button.dataset.searchTerm || '';

        const ajaxUrls = window.TYPO3?.settings?.ajaxUrls || {};
        const displayModalUrl = ajaxUrls.newsletter_display_send_message_modal;

        if (!displayModalUrl) {
            console.error('Send message URLs not found in TYPO3 settings');
            this.showError('Send message configuration error');
            return;
        }

        const sendUrl = this.getEditRecipientUrl(displayModalUrl, [], searchTerm);

        this.modal = Modal.advanced({
            type: Modal.types.ajax,
            title: 'Enqueue messages',
            severity: top.TYPO3.Severity.notice,
            content: sendUrl,
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
                    text: 'Send',
                    btnClass: 'btn btn-primary',
                    trigger: () => {
                        this.performSendMessage(searchTerm);
                    },
                },
            ],
        });
    }

    /**
     * Perform the actual send message operation
     */
    async performSendMessage(searchTerm) {
        // Disable buttons during operation
        const buttons = this.modal.querySelectorAll('.btn');
        buttons.forEach(btn => btn.disabled = true);

        const form = window.parent.document.querySelector('#form-bulk-send');
        if (!form) {
            this.showError('Send form not found');
            return;
        }

        const isTestMessage = window.parent.document.querySelector('#has-body-test')?.value === '1';
        
        const ajaxUrls = window.TYPO3?.settings?.ajaxUrls || {};
        const updateUrl = isTestMessage
            ? ajaxUrls.newsletter_send_test_messages
            : ajaxUrls.newsletter_enqueue_messages;

        if (!updateUrl) {
            this.showError('Send URL not configured');
            return;
        }

        const finalUpdateUrl = this.getEditRecipientUrl(updateUrl, [], searchTerm);

        try {
            const formData = new FormData(form);
            const response = await fetch(finalUpdateUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (response.ok) {
                const result = await response.text();
                
                if (result) {
                    this.showSuccess(result);
                    Modal.dismiss();
                } else {
                    this.showError('No response from server');
                }
                
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            console.error('Send message error:', error);
            this.showError('Send message failed: ' + error.message);
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
const enqueueMessages = new MessengerEnqueueMessages();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => enqueueMessages.initialize());
} else {
    enqueueMessages.initialize();
}

// Make available globally
window.MessengerEnqueueMessages = enqueueMessages;

export default MessengerEnqueueMessages;
