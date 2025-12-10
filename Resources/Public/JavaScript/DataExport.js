/**
 * Module: Fab/Messenger/DataExport
 */
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

const MessengerDataExport = {
    /**
     * Get export storage URL.
     *
     * @param {string} url
     * @param format
     * @param module
     * @param type
     * @param searchTerm
     * @return string
     * @private
     */
    getExportStorageUrl: function (url, format, module, type, searchTerm = '') {

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
        params.append('format', format);
        params.set('dataType', type);
        params.set('search', searchTerm);
        params.set('module', module);

        const finalUrl = urlObj.toString();

        return finalUrl;
    },

    initialize: function () {
        this.initializeDataExport();
    },

    /**
     * @return void
     */
    initializeDataExport: function () {

        document.addEventListener('click', function (e) {
            const button = e.target.closest('.btn-export');
            if (!button) {
                return;
            }

            e.preventDefault();
            const format = button.dataset.format;
            const module = button.dataset.module;
            const type = button.dataset.dataType;
            const searchTerm = button.dataset.searchTerm || '';

            if (!window.TYPO3 || !window.TYPO3.settings || !window.TYPO3.settings.ajaxUrls) {
                Notification.error('Error', 'TYPO3 configuration not loaded. Please refresh the page.');
                return;
            }

            const url = MessengerDataExport.getExportStorageUrl(
                TYPO3.settings.ajaxUrls.messenger_export_data_confirm,
                format,
                module,
                type,
                searchTerm,
            );

            MessengerDataExport.modal = Modal.advanced({
                type: Modal.types.ajax,
                title: `Export as ${format}`,
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
                        text: 'Export data',
                        btnClass: 'btn btn-primary',
                        trigger: function () {

                            const modalElement = MessengerDataExport.modal.find ? MessengerDataExport.modal.find('.modal-content')[0] : MessengerDataExport.modal;
                            if (modalElement) {
                                const buttons = modalElement.querySelectorAll('.btn');
                                buttons.forEach(btn => btn.setAttribute('disabled', 'disabled'));
                            } else {
                                console.log('Modal element not found for button disabling');
                            }

                            const exportUrl = MessengerDataExport.getExportStorageUrl(
                                TYPO3.settings.ajaxUrls.messenger_export_data_export,
                                format,
                                module,
                                type,
                                searchTerm,
                            );

                            window.location.href = exportUrl;

                            setTimeout(function() {
                                Modal.dismiss();
                            }, 500);
                        },
                    },
                ],
            });
        });
    },

};

window.MessengerDataExport = MessengerDataExport;

document.addEventListener('DOMContentLoaded', () => {
    MessengerDataExport.initialize();
});
