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

      if (columnsToSend !== '') {
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

        let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);
        let dataCount = columnsToSend.length;
        const url = Messenger.getEditStorageUrl(TYPO3.settings.ajaxUrls.send_again_confirmation);

        Messenger.modal = Modal.advanced({
          type: Modal.types.ajax,
          title: 'Send Again',
          severity: top.TYPO3.Severity.notice,
          content: url,
          button: [
            {
              text: 'Annuler',
              btnClass: 'btn btn-default',
              trigger: function () {
                Modal.dismiss();
              },
            },
            {
              text: 'Confirmer',
              btnClass: 'btn btn-primary',
              trigger: function () {
                // Disable button
                $('.btn', Messenger.modal).attr('disabled', 'disabled');

                // Generate the dequeue URL
                const sendAgainUrl = url.replace('confirm&', 'sendAgain&');

                // Ajax request
                $.ajax({
                  url: sendAgainUrl,
                  type: 'POST',

                  data: {
                    tx_messenger_user_messengerm1: {
                      matches: {
                        uid: columnsToSend.join(','),
                      },
                    },
                  },

                  controller: 'MessageSent',
                  action: 'sendAgain',

                  success: function () {
                    Notification.success('Message(s) sent successfully');
                    Modal.dismiss();
                  },
                  error: function () {
                    Notification.error('An error occurred while sending message(s)');
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
  return Messenger;
});
