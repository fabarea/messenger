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
     * @param type
     * @param searchTerm
     * @return string
     * @private
     */
    getEditStorageUrl: function (url, type, searchTerm = '') {
      const uri = new Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);
      uri.addQueryParam(
        'tx_messenger_user_messengerm1[matches][uid]',
        columnsToSend.join(',') + '&dataType=' + type + '&search=' + searchTerm,
      );
      return decodeURIComponent(uri.toString());
    },

    getEditRecipientUrl: function (url, data = [], searchTerm = '') {
      const uri = new Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);
      uri.addQueryParam(
        'tx_messenger_user_messengerm5[matches][uid]',
        columnsToSend.join(',') + '&data=' + data + '&search=' + searchTerm,
      );
      return decodeURIComponent(uri.toString());
    },

    getExportStorageUrl: function (url, format, module, type, searchTerm = '') {
      const uri = new Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

      uri.addQueryParam(
        module + '[matches][uid]',
        columnsToSend.join(',') + '&format=' + format + '&dataType=' + type + '&search=' + searchTerm,
      );

      return decodeURIComponent(uri.toString());
    },

    /**
     * @return void
     */
    initialize: function () {
      this.initializeExport();
      this.initializeSendAgainConfirmation();
      this.initializeUpdateRecipients();
      this.initializeSendMessage();
    },

    /**
     * @return void
     */
    initializeSendAgainConfirmation: function () {
      $(document).on('click', '.btn-sendAgain', function (e) {
        e.preventDefault();

        const type = $(this).data('data-type');
        const searchTerm = $(this).data('search-term');
        const url = Messenger.getEditStorageUrl(
          TYPO3.settings.ajaxUrls.messenger_send_again_confirmation,
          type,
          searchTerm,
        );
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
                const sendAgainUrl = Messenger.getEditStorageUrl(
                  TYPO3.settings.ajaxUrls.messenger_send_again,
                  type,
                  searchTerm,
                );
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

        const url = Messenger.getExportStorageUrl(
          TYPO3.settings.ajaxUrls.messenger_export_data,
          format,
          module,
          type,
          searchTerm,
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
                const exportUrl = Messenger.getExportStorageUrl(
                  TYPO3.settings.ajaxUrls.messenger_export_data_validation,
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
                      window.location.href = Messenger.getExportStorageUrl(
                        TYPO3.settings.ajaxUrls.messenger_export_data_validation,
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

    /**
     * @return void
     */
    initializeUpdateRecipients: function () {
      $(document).on('click', '.btn-update-recipient', function (e) {
        e.preventDefault();
        const searchTerm = $(this).data('search-term');
        const url = Messenger.getEditRecipientUrl(TYPO3.settings.ajaxUrls.newsletter_update_recipient, searchTerm);
        Messenger.modal = Modal.advanced({
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
                $('.btn', Messenger.modal).attr('disabled', 'disabled');

                const form = window.parent.document.querySelector('#form-update-many-recipients');

                const url = Messenger.getEditRecipientUrl(
                  TYPO3.settings.ajaxUrls.newsletter_update_recipient_save,
                  searchTerm,
                );
                $.ajax({
                  url: url,
                  data: new URLSearchParams(new FormData(form)).toString(),
                  method: 'post',

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
    },
    /**
     * @return void
     */
    initializeSendMessage: function () {
      $(document).on('click', '.btn-send-message', function (e) {
        e.preventDefault();
        const searchTerm = $(this).data('search-term');

        const sendUrl = Messenger.getEditRecipientUrl(
          TYPO3.settings.ajaxUrls.newsletter_send_message_from_clipboard,
          [],
          searchTerm,
        );
        Messenger.modal = Modal.advanced({
          type: Modal.types.ajax,
          title: 'Update recipient',
          severity: top.TYPO3.Severity.notice,
          content: sendUrl,
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
              text: 'Send',
              btnClass: 'btn btn-primary',
              trigger: function () {
                $('.btn', Messenger.modal).attr('disabled', 'disabled');

                const updateUrl = Messenger.getEditRecipientUrl(
                  TYPO3.settings.ajaxUrls.newsletter_send_message_from_enqueue,
                  [],
                  searchTerm,
                );
                const form = window.parent.document.querySelector('#form-bulk-send');

                $.ajax({
                  url: updateUrl,
                  data: new URLSearchParams(new FormData(form)).toString(),
                  method: 'post',

                  success: function (response) {
                    if (response) {
                      Notification.success('Success', response);
                      Modal.dismiss();
                    } else {
                      Notification.error('Error', response);
                      $('.btn').removeAttr('disabled');
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
