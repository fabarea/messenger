/**
 * Module: Fab/Messenger/Media
 */
define(['jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Notification'], function ($, Modal, Notification) {
  'use strict';

  const MessengerDataExport = {
    /**
     * Get edit storage URL.
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
      const uri = new Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

      uri.addQueryParam(
        'tx_messenger_user_messenger' + '[matches][uid]',
        columnsToSend.join(',') +
          '&format=' +
          format +
          '&dataType=' +
          type +
          '&search=' +
          searchTerm +
          '&module=' +
          module,
      );

      return decodeURIComponent(uri.toString());
    },

    /**
     * @return void
     */
    initialize: function () {
      this.initializeExport();
    },

    /**
     * @return void
     */

    initializeExport: function () {
      $(document).on('click', '.btn-export', function (e) {
        e.preventDefault();

        const format = $(this).data('format');
        const module = $(this).data('module');
        const type = $(this).data('data-type');
        const searchTerm = $(this).data('search-term');

        const url = MessengerDataExport.getExportStorageUrl(
          TYPO3.settings.ajaxUrls.messenger_export_data_confirm,
          format,
          module,
          type,
          searchTerm,
        );
        MessengerDataExport.modal = Modal.advanced({
          type: Modal.types.ajax,
          title: 'Export as ' + format,
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
                $('.btn', MessengerDataExport.modal).attr('disabled', 'disabled');
                const exportUrl = MessengerDataExport.getExportStorageUrl(
                  TYPO3.settings.ajaxUrls.messenger_export_data_export,
                  format,
                  module,
                  type,
                  searchTerm,
                );
                // Ajax request
                $.ajax({
                  url: exportUrl,

                  /**
                   * On success call back
                   *
                   * @param response
                   */
                  success: function (response) {
                    if (response) {
                      window.location.href = MessengerDataExport.getExportStorageUrl(
                        TYPO3.settings.ajaxUrls.messenger_export_data_export,
                        format,
                        module,
                        type,
                        searchTerm,
                      );
                      Modal.dismiss();
                    } else {
                      Notification.error('Error', response);
                      Modal.dismiss();
                    }
                  },
                });
              },
            },
          ],
        });
      });
    },
  };

  MessengerDataExport.initialize();
  return MessengerDataExport;
});
