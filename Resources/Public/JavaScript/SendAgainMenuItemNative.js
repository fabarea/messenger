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

    /**
     * @return void
     */
    initialize: function () {
      $(document).on('click', '.btn-sendAgain', function (e) {
        e.preventDefault();
        const url = Messenger.getEditStorageUrl(TYPO3.settings.ajaxUrls.send_again_confirmation);
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
                const sendAgainUrl = Messenger.getEditStorageUrl(TYPO3.settings.ajaxUrls.send_again);


                // Ajax request
                $.ajax({
                  url: sendAgainUrl,



                  /**
                   * On success call back
                   *
                   * @param response
                   */
                  success: function(response) {
                  Notification.success('', response);
                  Modal.dismiss();
                }
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
