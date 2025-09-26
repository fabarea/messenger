/**
 * Module: Fab/Messenger/MassDeletion
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

const MessengerMassDeletion = {
    /**
     * Get mass deletion storage URL.
     *
     * @param {string} url
     * @param module
     * @param type
     * @param searchTerm
     * @return string
     * @private
     */
    getMassDeletionStorageUrl: function (url, module, type, searchTerm = '') {

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
            if (!e.target.classList.contains('btn-delete-selected')) {
                return;
            }

            e.preventDefault();

            const button = e.target;
            const module = button.dataset.module;
            const type = button.dataset.dataType;
            const searchTerm = button.dataset.searchTerm || '';

            if (!window.TYPO3 || !window.TYPO3.settings || !window.TYPO3.settings.ajaxUrls) {
                Notification.error('Error', 'TYPO3 configuration not loaded. Please refresh the page.');
                return;
            }

            const url = MessengerMassDeletion.getMassDeletionStorageUrl(
                TYPO3.settings.ajaxUrls.messenger_mass_deletion_confirm,
                module,
                type,
                searchTerm,
            );

            MessengerMassDeletion.modal = Modal.advanced({
                type: Modal.types.ajax,
                title: 'Confirm Mass Deletion',
                severity: top.TYPO3.Severity.warning,
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
                        text: 'Delete selected',
                        btnClass: 'btn btn-danger',
                        trigger: function () {

                            const modalElement = MessengerMassDeletion.modal.find ? MessengerMassDeletion.modal.find('.modal-content')[0] : MessengerMassDeletion.modal;
                            if (modalElement) {
                                const buttons = modalElement.querySelectorAll('.btn');
                                buttons.forEach(btn => btn.setAttribute('disabled', 'disabled'));
                            } else {
                                console.log('Modal element not found for button disabling');
                            }

                            const deleteUrl = MessengerMassDeletion.getMassDeletionStorageUrl(
                                TYPO3.settings.ajaxUrls.messenger_mass_deletion_delete,
                                module,
                                type,
                                searchTerm,
                            );

                            // Use AjaxRequest for deletion operations
                            new AjaxRequest(deleteUrl)
                                .get()
                                .then(async (response) => {
                                    const data = await response.resolve();

                                    if (typeof data === 'string') {
                                        Notification.success('Success', data);

                                        // Reload the page to refresh the data
                                        setTimeout(function() {
                                            window.location.reload();
                                        }, 1000);

                                        Modal.dismiss();
                                    } else {
                                        throw new Error('Unexpected response format');
                                    }
                                })
                                .catch((error) => {
                                    console.error('Deletion error:', error);
                                    Notification.error('Error', 'Deletion operation failed. Please try again.');

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
window.MessengerMassDeletion = MessengerMassDeletion;

document.addEventListener('DOMContentLoaded', () => {
    MessengerMassDeletion.initialize();
});
