/**
 * Module: Fab/Messenger/MassDeletion
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

const MessengerMassDeletion = {
    /**
     * Get edit storage URL.
     *
     * @param {string} url
     * @param module
     * @param type
     * @param searchTerm
     * @return string
     * @private
     */

    getMassDeletionUrl: function (url, module, type, searchTerm = '') {

        let absoluteUrl;
        if (url.startsWith('/')) {
            absoluteUrl = window.location.origin + url;
        } else {
            absoluteUrl = url;
        }

        let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

        const urlObj = new URL(absoluteUrl);
        const params = urlObj.searchParams;

        params.set('tx_messenger_user_messenger[matches][uid]', columnsToSend.join(','));
        params.set('dataType', type);
        params.set('search', searchTerm);
        params.set('module', module);

        const finalUrl = urlObj.toString();

        return finalUrl;
    },

    initialize: function () {
        this.initializeMassDeletion();
    },

    /**
     * @return void
     */
    initializeMassDeletion: function () {

        document.addEventListener('click', function (e) {
            const button = e.target.closest('.btn-mass-delete');
            if (!button) {
                return;
            }

            e.preventDefault();
            const module = button.dataset.module;
            const type = button.dataset.dataType;
            const searchTerm = button.dataset.searchTerm;


            if (!window.TYPO3 || !window.TYPO3.settings || !window.TYPO3.settings.ajaxUrls) {
                Notification.error('Error', 'TYPO3 configuration not loaded. Please refresh the page.');
                return;
            }

            const url = MessengerMassDeletion.getMassDeletionUrl(
                TYPO3.settings.ajaxUrls.messenger_confirm_mass_delete,
                module,
                type,
                searchTerm,
            );


            MessengerMassDeletion.modal = Modal.advanced({
                type: Modal.types.ajax,
                title: 'Delete',
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
                        text: 'Delete ',
                        btnClass: 'btn btn-primary',
                        trigger: function () {

                            const modalElement = MessengerMassDeletion.modal.find ? MessengerMassDeletion.modal.find('.modal-content')[0] : MessengerMassDeletion.modal;
                            if (modalElement) {
                                const buttons = modalElement.querySelectorAll('.btn');
                                buttons.forEach(btn => btn.setAttribute('disabled', 'disabled'));
                            } else {
                                console.log('Modal element not found for button disabling');
                            }

                            const deleteUrl = MessengerMassDeletion.getMassDeletionUrl(
                                TYPO3.settings.ajaxUrls.messenger_mass_delete,
                                module,
                                type,
                                searchTerm,
                            );

                            // Try POST method instead of GET for delete operations
                            new AjaxRequest(deleteUrl)
                                .post({})
                                .then(async (response) => {
                                    const data = await response.resolve();
                                    Notification.success('Success', 'Items deleted successfully');
                                    Modal.dismiss();
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                })
                                .catch((error) => {

                                    new AjaxRequest(deleteUrl)
                                        .get()
                                        .then(async (response) => {
                                            const data = await response.resolve();
                                            Notification.success('Success', 'Items deleted successfully');
                                            Modal.dismiss();
                                            setTimeout(() => {
                                                window.location.reload();
                                            }, 1000);
                                        })
                                        .catch((fallbackError) => {
                                            Notification.error('Error', 'Delete operation failed. Please try again.');

                                            if (modalElement) {
                                                const buttons = modalElement.querySelectorAll('.btn');
                                                buttons.forEach(btn => btn.removeAttribute('disabled'));
                                            }
                                        });
                                });
                        },
                    },
                ],
            });
        });
    },
};

// Expose globally for compatibility
window.MessengerMassDeletion = MessengerMassDeletion;
window.MessengerMassDeletion.initialized = false;

// Initialize immediately if DOM is already loaded, otherwise wait for DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.MessengerMassDeletion.initialize();
    });
} else {
    window.MessengerMassDeletion.initialize();
}
