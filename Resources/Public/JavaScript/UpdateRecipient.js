/**
 * Module: Fab/Messenger/UpdateRecipient
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

const MessengerUpdateRecipient = {
    /**
     * Get edit recipient URL
     *
     * @param {string} url
     * @param {Array} data
     * @param {string} searchTerm
     * @return {string}
     * @private
     */
    getEditRecipientUrl: function (url, data = [], searchTerm = '') {
        if (!url) {
            console.error('URL is undefined or null');
            return '';
        }

        let absoluteUrl;
        if (url.startsWith('/')) {
            absoluteUrl = window.location.origin + url;
        } else {
            absoluteUrl = url;
        }

        // Get selected items
        const columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

        const urlObj = new URL(absoluteUrl);
        const params = urlObj.searchParams;

        params.set('tx_messenger_user_messenger[matches][uid]', columnsToSend.join(','));
        params.set('data', data);
        params.set('search', searchTerm);

        const finalUrl = urlObj.toString();

        return finalUrl;
    },

    initialize: function () {
        this.initializeUpdateRecipient();
    },

    /**
     * Initialize update recipient functionality
     * @return void
     */
    initializeUpdateRecipient: function () {
        document.addEventListener('click', function (e) {
            const button = e.target.closest('.btn-update-recipient');
            if (!button) {
                return;
            }

            e.preventDefault();
            const searchTerm = button.dataset.searchTerm || '';

            if (!window.TYPO3 || !window.TYPO3.settings || !window.TYPO3.settings.ajaxUrls) {
                Notification.error('Error', 'TYPO3 configuration not loaded. Please refresh the page.');
                return;
            }

            const updateUrl = TYPO3.settings.ajaxUrls.newsletter_update_recipient;
            const saveUrl = TYPO3.settings.ajaxUrls.newsletter_update_recipient_save;

            if (!updateUrl || !saveUrl) {
                console.error('Update recipient URLs not found in TYPO3 settings');
                Notification.error('Error', 'Update recipient configuration error');
                return;
            }

            const url = MessengerUpdateRecipient.getEditRecipientUrl(updateUrl, [], searchTerm);

            MessengerUpdateRecipient.modal = Modal.advanced({
                type: Modal.types.ajax,
                title: 'Update recipient',
                severity: top.TYPO3.Severity.notice,
                content: url,
                staticBackdrop: false,
                buttons: [
                    {
                        text: 'Cancel',
                        btnClass: 'btn btn-default',
                        trigger: function () {
                            Modal.dismiss();
                        },
                    },
                    {
                        text: 'Update recipient',
                        btnClass: 'btn btn-primary',
                        trigger: function () {
                            const modalElement = MessengerUpdateRecipient.modal.find ? MessengerUpdateRecipient.modal.find('.modal-content')[0] : MessengerUpdateRecipient.modal;
                            if (modalElement) {
                                const buttons = modalElement.querySelectorAll('.btn');
                                buttons.forEach(btn => btn.setAttribute('disabled', 'disabled'));
                            } else {
                                console.log('Modal element not found for button disabling');
                            }

                            const form = window.parent.document.querySelector('#form-update-many-recipients');
                            if (!form) {
                                Notification.error('Error', 'Update form not found');
                                return;
                            }

                            const finalSaveUrl = MessengerUpdateRecipient.getEditRecipientUrl(saveUrl, [], searchTerm);
                            const formData = new FormData(form);

                            // Try POST method first
                            fetch(finalSaveUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json'
                                },
                                body: formData
                            })
                            .then(async (response) => {
                                if (response.ok) {
                                    const result = await response.text();
                                    Notification.success('Success', 'Recipients updated successfully');
                                    Modal.dismiss();
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 500);
                                } else {
                                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                                }
                            })
                            .catch((error) => {
                                console.error('Update recipient error:', error);
                                Notification.error('Error', 'Update recipient failed: ' + error.message);

                                if (modalElement) {
                                    const buttons = modalElement.querySelectorAll('.btn');
                                    buttons.forEach(btn => btn.removeAttribute('disabled'));
                                }
                            });
                        },
                    },
                ],
            });
        });
    },
};

// Expose globally for compatibility
window.MessengerUpdateRecipient = MessengerUpdateRecipient;
window.MessengerUpdateRecipient.initialized = false;

document.addEventListener('DOMContentLoaded', () => {
    window.MessengerUpdateRecipient.initialize();
});

export default MessengerUpdateRecipient;
