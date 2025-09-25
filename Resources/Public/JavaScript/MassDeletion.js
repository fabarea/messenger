/**
 * Module: Fab/Messenger/MassDeletion
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';

class MessengerMassDeletion {
    constructor() {
        this.initialized = false;
        this.modal = null;
    }

    /**
     * Get mass deletion URL
     */
    getMassDeletionUrl(url, module, type, searchTerm = '') {
        const uri = new URL(url, window.location.origin);

        // Get selected items
        const columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

        const params = new URLSearchParams();
        params.append('tx_messenger_user_messenger[matches][uid]', columnsToSend.join(','));
        params.append('dataType', type);
        params.append('search', searchTerm);
        params.append('module', module);

        uri.search = params.toString();
        return uri.toString();
    }

    /**
     * Initialize mass deletion functionality
     */
    initialize() {
        if (this.initialized) return;

        document.addEventListener('click', (e) => {
            const deleteButton = e.target.closest('.btn-mass-delete');
            if (deleteButton) {
                e.preventDefault();
                this.handleMassDeleteClick(deleteButton);
            }
        });

        this.initialized = true;
    }

    /**
     * Handle mass delete button click
     */
    handleMassDeleteClick(button) {
        const module = button.dataset.module;
        const type = button.dataset.dataType;
        const searchTerm = button.dataset.searchTerm || '';

        const ajaxUrls = window.TYPO3?.settings?.ajaxUrls || {};
        const confirmUrl = ajaxUrls.messenger_confirm_mass_delete;
        const deleteUrl = ajaxUrls.messenger_mass_delete;

        if (!confirmUrl || !deleteUrl) {
            console.error('Mass deletion URLs not found in TYPO3 settings');
            this.showError('Mass deletion configuration error');
            return;
        }

        const url = this.getMassDeletionUrl(confirmUrl, module, type, searchTerm);

        this.modal = Modal.advanced({
            type: Modal.types.ajax,
            title: 'Delete',
            severity: top.TYPO3.Severity.warning,
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
                    text: 'Delete',
                    btnClass: 'btn btn-danger',
                    trigger: () => {
                        this.performMassDeletion(deleteUrl, module, type, searchTerm);
                    },
                },
            ],
        });
    }

    /**
     * Perform the actual mass deletion
     */
    async performMassDeletion(deleteUrl, module, type, searchTerm) {
        // Disable buttons during deletion
        const buttons = this.modal.querySelectorAll('.btn');
        buttons.forEach(btn => btn.disabled = true);

        const finalDeleteUrl = this.getMassDeletionUrl(deleteUrl, module, type, searchTerm);

        try {
            const response = await fetch(finalDeleteUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });

            if (response.ok) {
                const result = await response.text(); // Mass deletion might return plain text
                
                this.showSuccess('Items deleted successfully');
                Modal.dismiss();
                
                // Reload the page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 500);
                
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            console.error('Mass deletion error:', error);
            this.showError('Mass deletion failed: ' + error.message);
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
const massDeletion = new MessengerMassDeletion();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => massDeletion.initialize());
} else {
    massDeletion.initialize();
}

// Make available globally
window.MessengerMassDeletion = massDeletion;

export default MessengerMassDeletion;
