/**
 * Module: Fab/Messenger/UpdateRecipient
 */
import $ from 'jquery';
import { Modal } from '@typo3/backend/modal';
import { Notification } from '@typo3/backend/notification';
import { Uri } from './Utils/UriWrapper.js';

const MessengerUpdateRecipient = {
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

    initialize: function () {
      this.initializeUpdateRecipients();
    },

    /**
     * @return void
     */
    initializeUpdateRecipients: function () {
      $(document).on('click', '.btn-update-recipient', function (e) {
        e.preventDefault();
        const searchTerm = $(this).data('search-term');
        const url = MessengerUpdateRecipient.getEditRecipientUrl(
          TYPO3.settings.ajaxUrls.newsletter_update_recipient,
          searchTerm,
        );
        MessengerUpdateRecipient.modal = Modal.advanced({
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
                $('.btn', MessengerUpdateRecipient.modal).attr('disabled', 'disabled');

                const form = window.parent.document.querySelector('#form-update-many-recipients');

                const url = MessengerUpdateRecipient.getEditRecipientUrl(
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
                    // Refresh the page to show updated recipients
                    window.location.reload();
                  },
                });
              },
            },
          ],
        });
      });
    },
  };

MessengerUpdateRecipient.initialize();
export default MessengerUpdateRecipient;
