/**
 * Module: Fab/Messenger/SendAgainMenuItemNative
 */
define(['jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Notification'], function($, Modal, Notification) {
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
      const uri = new window.Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);
      uri.addQueryParam(
        'tx_messenger_user_messenger[matches][uid]',
        columnsToSend.join(',') + '&dataType=' + type + '&search=' + searchTerm,
      );
      return decodeURIComponent(uri.toString());
    },
    
    /**
     * @return void
     */
    initialize: function () {
      this.initializeSendAgainConfirmation();
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
  };

    Messenger.initialize();

    // Expose globally for compatibility
    window.MessengerSendAgain = Messenger;

    return Messenger;
});
