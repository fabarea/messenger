/**
 * Module: Fab/Messenger/SendAgainMenuItemNative
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

const MessengerSendAgain = {
    /**
     * Get edit storage URL
     *
     * @param {string} url
     * @param type
     * @param searchTerm
     * @return string
     * @private
     */
    getEditStorageUrl: function (url, type, searchTerm = '') {

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
        params.set('dataType', type);
        params.set('search', searchTerm);

        const finalUrl = urlObj.toString();

        return finalUrl;
    },

    initialize: function () {
        this.initializeSendAgain();
    },

    /**
     * @return void
     */
    initializeSendAgain: function () {

        document.addEventListener('click', function (e) {
            const button = e.target.closest('.btn-sendAgain');
            if (!button) {
                return;
            }

            e.preventDefault();
            const type = button.dataset.dataType;
            const searchTerm = button.dataset.searchTerm || '';

            if (!window.TYPO3 || !window.TYPO3.settings || !window.TYPO3.settings.ajaxUrls) {
                Notification.error('Error', 'TYPO3 configuration not loaded. Please refresh the page.');
                return;
            }

            // Check if the required AJAX URLs exist
            if (!TYPO3.settings.ajaxUrls.messenger_send_again_confirmation || !TYPO3.settings.ajaxUrls.messenger_send_again) {
                console.error('Send again AJAX URLs not found:', TYPO3.settings.ajaxUrls);
                Notification.error('Error', 'Send again URLs not configured. Please check TYPO3 configuration.');
                return;
            }

            const url = MessengerSendAgain.getEditStorageUrl(
                TYPO3.settings.ajaxUrls.messenger_send_again_confirmation,
                type,
                searchTerm,
            );

            // Check if URL was successfully generated
            if (!url) {
                Notification.error('Error', 'Failed to generate send again URL.');
                return;
            }

            MessengerSendAgain.modal = Modal.advanced({
                type: Modal.types.ajax,
                title: 'Send Again',
                severity: top.TYPO3.Severity.notice,
                content: url,
                buttons: [
                    {
                        text: 'Cancel',
                        btnClass: 'btn btn-default',
                        trigger: function () {
                            Modal.dismiss();
                        },
                    },
                    {
                        text: 'Send Again',
                        btnClass: 'btn btn-primary',
                        trigger: function () {

                            const modalElement = MessengerSendAgain.modal.find ? MessengerSendAgain.modal.find('.modal-content')[0] : MessengerSendAgain.modal;
                            if (modalElement) {
                                const buttons = modalElement.querySelectorAll('.btn');
                                buttons.forEach(btn => btn.setAttribute('disabled', 'disabled'));
                            } else {
                                console.log('Modal element not found for button disabling');
                            }

                            const sendAgainUrl = MessengerSendAgain.getEditStorageUrl(
                                TYPO3.settings.ajaxUrls.messenger_send_again,
                                type,
                                searchTerm,
                            );

                            // Use AjaxRequest for send again operations
                            new AjaxRequest(sendAgainUrl)
                                .post({})
                                .then(async (response) => {
                                    const data = await response.resolve();

                                    if (typeof data === 'string' || data.success !== false) {
                                        Notification.success('Success', 'Messages sent again successfully');

                                        // Reload the page to refresh the data
                                        setTimeout(function() {
                                            window.location.reload();
                                        }, 500);

                                        Modal.dismiss();
                                    } else {
                                        throw new Error(data.message || 'Send again failed');
                                    }
                                })
                                .catch((error) => {
                                    console.error('Send again error:', error);
                                    Notification.error('Error', 'Send again operation failed. Please try again.');

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
window.MessengerSendAgain = MessengerSendAgain;

document.addEventListener('DOMContentLoaded', () => {
    MessengerSendAgain.initialize();
});
