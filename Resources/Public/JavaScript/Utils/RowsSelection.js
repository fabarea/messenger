/**
 * RowsSelection module for TYPO3 v12+ with ES6 imports
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';

class MessengerRowsSelection {
    constructor() {
        this.initialized = false;
    }

    deleteItem(element) {
        const modal = Modal.advanced({
            type: Modal.types.default,
            title: 'Delete Message',
            severity: top.TYPO3.Severity.warning,
            content: document.createElement('div').innerHTML = `Are you sure you want to delete this message from <strong>${element.dataset.name}</strong>?<br><br><small class="text-muted">This action cannot be undone.</small>`,
            buttons: [
                {
                    text: 'Cancel',
                    btnClass: 'btn btn-default',
                    trigger: function () {
                        Modal.dismiss();
                    },
                },
                {
                    text: 'Delete',
                    btnClass: 'btn btn-danger',
                    trigger: function () {
                        Modal.dismiss();
                        // Small delay to ensure modal is closed before redirect
                        setTimeout(function() {
                            window.location.href = element.dataset.deleteUrl;
                        }, 100);
                    },
                },
            ],
        });

        return modal;
    }

    selectAll() {
        const updateSelectionState = () => {
            const recordAllCheckbox = document.getElementById('record-all');
            const isChecked = recordAllCheckbox ? recordAllCheckbox.checked : false;
            const checkboxes = document.querySelectorAll('.checkboxes .select');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                checkbox.dispatchEvent(new Event('change'));
            });
        };

        const recordAllCheckbox = document.getElementById('record-all');
        if (recordAllCheckbox) {
            recordAllCheckbox.addEventListener('change', updateSelectionState);
            updateSelectionState();
        }
    }

    getSelectedItems() {
        return [...document.querySelectorAll('.select:checked')].map((element) => element.value);
    }

    initialize() {
        if (this.initialized) return;

        // Initialize select all functionality
        this.selectAll();

        // Handle items per page change
        const itemsPerPage = document.getElementById('itemsPerPage');
        if (itemsPerPage) {
            itemsPerPage.addEventListener('change', function() {
                const form = this.closest('form');
                if (form) form.submit();
            });
        }

        // Handle delete item clicks
        document.addEventListener('click', (e) => {
            const deleteButton = e.target.closest('[data-action="delete-item"]');
            if (deleteButton) {
                e.preventDefault();
                this.deleteItem(deleteButton);
            }
        });

        // Initialize other messenger modules
        this.initializeOtherModules();

        this.initialized = true;
    }

    initializeOtherModules() {
        const modules = [
            'MessengerMassDeletion',
            'MessengerDataExport',
            'MessengerEnqueueMessages',
            'MessengerSendAgain',
            'MessengerUpdateRecipient'
        ];

        modules.forEach(moduleName => {
            if (window[moduleName] && !window[moduleName].initialized) {
                try {
                    if (typeof window[moduleName].initialize === 'function') {
                        window[moduleName].initialize();
                        window[moduleName].initialized = true;
                    }
                } catch (error) {
                    console.warn(`Failed to initialize ${moduleName}:`, error);
                }
            }
        });
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const messenger = new MessengerRowsSelection();
        messenger.initialize();
        window.Messenger = messenger;
    });
} else {
    const messenger = new MessengerRowsSelection();
    messenger.initialize();
    window.Messenger = messenger;
}

export default MessengerRowsSelection;
