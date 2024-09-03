/**
 * Module: Fab/Messenger/Media
 */
define(['jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Notification'], function ($, Modal, Notification) {
  'use strict';

  const Messenger = {
    /**
     * Get edit storage URL.
     *
     * @param {string} url
     * @return string
     * @private
     */

    getEditStorageUrl: function (url) {
      var uri = new Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

      if (columnsToSend.length > 0) {
        uri.addQueryParam('tx_messenger_user_messengerm1[matches][uid]', columnsToSend.join(','));
      }
      return decodeURIComponent(uri.toString());
    },

    getExportStorageUrl: function (url, format, module, repository) {
      var uri = new Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

      if (columnsToSend.length > 0) {
        uri.addQueryParam(
          module + '[matches][uid]',
          columnsToSend.join(',') + '&format=' + format + '&repository=' + repository,
        );
      }
      return decodeURIComponent(uri.toString());
    },

    /**
     * @return void
     */
    initialize: function () {
      $(document).on('click', '.btn-sendAgain', function (e) {
        if ($('.select:checked').length === 0) {
          Notification.error('Error', 'Please select at least one item');
          return;
        }
        e.preventDefault();
        const url = Messenger.getEditStorageUrl(TYPO3.settings.ajaxUrls.messenger_send_again_confirmation);
        Messenger.modal = Modal.advanced({
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
                $('.btn', Messenger.modal).attr('disabled', 'disabled');
                const sendAgainUrl = Messenger.getEditStorageUrl(TYPO3.settings.ajaxUrls.messenger_send_again);
                // Ajax request
                $.ajax({
                  url: sendAgainUrl,

                  /**
                   * On success call back
                   *
                   * @param response
                   */
                  success: function (response) {
                    Notification.success('', response);
                    Modal.dismiss();
                  },
                });
              },
            },
          ],
        });
      });

      $(document).on('click', '.btn-export', function (e) {
        if ($('.select:checked').length === 0) {
          Notification.error('Error', 'Please select at least one item');
          return;
        }
        e.preventDefault();

        const format = $(this).data('format');
        const module = $(this).data('module');
        const repository = $(this).data('repository');

        const url = Messenger.getExportStorageUrl(
          TYPO3.settings.ajaxUrls.messenger_export_data,
          format,
          module,
          repository,
        );

        Messenger.modal = Modal.advanced({
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
                $('.btn', Messenger.modal).attr('disabled', 'disabled');
                const exportUrl = Messenger.getExportStorageUrl(TYPO3.settings.ajaxUrls.messenger_export_data_validation,  format,
                  module,
                  repository);
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
                      window.location.href = Messenger.getExportStorageUrl(
                        TYPO3.settings.ajaxUrls.messenger_export_data_validation,
                        format,
                        module,
                        repository,
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

  Messenger.initialize();
  return Messenger;
});
