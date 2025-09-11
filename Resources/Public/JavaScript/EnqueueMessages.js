/**
 * Module: Fab/Messenger/EnqueueMessages
 */
import $ from 'jquery';
import { Modal } from '@typo3/backend/modal';
import { Notification } from '@typo3/backend/notification';
import { Uri } from './Utils/UriWrapper.js';

const MessengerEnqueueMessages = {
    /**
     * Get edit storage URL.
     *
     * @param {string} url
     * @param data
     * @param searchTerm
     * @return string
     * @private
     */

    getEditRecipientUrl: function (url, data = [], searchTerm = '') {
      const uri = new Uri(url);
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);
      uri.addQueryParam(
        'tx_messenger_user_messengerm5[matches][uid]',
        columnsToSend.join(',') + '&data=' + data + '&search=' + searchTerm,
      );
      return decodeURIComponent(uri.toString());
    },

    /**
     * @return void
     */
    initialize: function () {
      this.initializeSendMessage();
    },

    initializeSendMessage: function () {
      $(document).on('click', '.btn-send-message', function (e) {
        e.preventDefault();
        const searchTerm = $(this).data('search-term');

        const sendUrl = MessengerEnqueueMessages.getEditRecipientUrl(
          TYPO3.settings.ajaxUrls.newsletter_display_send_message_modal,
          [],
          searchTerm,
        );

        MessengerEnqueueMessages.modal = Modal.advanced({
          type: Modal.types.ajax,
          title: 'Enqueue messages',
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
                $('.btn', MessengerEnqueueMessages.modal).attr('disabled', 'disabled');

                const isTestMessage = window.parent.document.querySelector('#has-body-test').value === '1';
                const updateUrl = isTestMessage
                  ? MessengerEnqueueMessages.getEditRecipientUrl(
                      TYPO3.settings.ajaxUrls.newsletter_send_test_messages,
                      [],
                      searchTerm,
                    )
                  : MessengerEnqueueMessages.getEditRecipientUrl(
                      TYPO3.settings.ajaxUrls.newsletter_enqueue_messages,
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

MessengerEnqueueMessages.initialize();

// Expose globally for compatibility
window.MessengerEnqueueMessages = MessengerEnqueueMessages;

export default MessengerEnqueueMessages;
