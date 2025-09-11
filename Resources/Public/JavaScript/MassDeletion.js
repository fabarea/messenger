/**
 * Module: Fab/Messenger/MassDeletion
 */
import $ from 'jquery';
import { Modal } from '@typo3/backend/modal';
import { Notification } from '@typo3/backend/notification';

const MessengerMassDeletion = {
    /**
     * Get edit storage URL.
     *
     * @param {string} url
     * @param module
     * @param type
     * @param searchTerm
     * @return string
     * @private
     */

    getMassDeletionUrl: function (url, module, type, searchTerm = '') {
      const uri = new Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);
      uri.addQueryParam(
        'tx_messenger_user_messenger' + '[matches][uid]',
        columnsToSend.join(',') + '&dataType=' + type + '&search=' + searchTerm + '&module=' + module,
      );

      return decodeURIComponent(uri.toString());
    },

    initialize: function () {
      this.initializeMassDeletion();
    },

    /**
     * @return void
     */
    initializeMassDeletion: function () {
      $(document).on('click', '.btn-mass-delete', function (e) {
        e.preventDefault();

        const module = $(this).data('module');
        const type = $(this).data('data-type');
        const searchTerm = $(this).data('search-term');
        const url = MessengerMassDeletion.getMassDeletionUrl(
          TYPO3.settings.ajaxUrls.messenger_confirm_mass_delete,
          module,
          type,
          searchTerm,
        );
        MessengerMassDeletion.modal = Modal.advanced({
          type: Modal.types.ajax,
          title: 'Delete',
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
              text: 'Delete ',
              btnClass: 'btn btn-primary',
              trigger: function () {
                $('.btn', MessengerMassDeletion.modal).attr('disabled', 'disabled');
                const deleteUrl = MessengerMassDeletion.getMassDeletionUrl(
                  TYPO3.settings.ajaxUrls.messenger_mass_delete,
                  module,
                  type,
                  searchTerm,
                );
                // Ajax request
                $.ajax({
                  url: deleteUrl,

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

MessengerMassDeletion.initialize();
export default MessengerMassDeletion;
