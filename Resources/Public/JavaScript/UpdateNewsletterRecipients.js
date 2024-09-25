// jshint ;_;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

define(['jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Notification'], function ($, Modal, Notification) {
  'use strict';

  const Messenger = {
    getEditRecipientUrl: function (url, recipient = '') {
      const uri = new Uri(url);

      // get element by columnsToSend value and assign to the uri object
      let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

      if (columnsToSend.length > 0) {
        uri.addQueryParam('tx_messenger_user_messengerm5[matches][uid]', columnsToSend.join(','), recipient);
      }
      return decodeURIComponent(uri.toString());
    },
    /**
     * @return void
     */
    initialize: function () {
      // Add listener on bulk send button
      $(document).on('click', '.btn-update-recipient', function (e) {
        if ($('.select:checked').length === 0) {
          Notification.error('Error', 'Please select at least one item');
          return;
        }
        e.preventDefault();

        const url = Messenger.getEditRecipientUrl(TYPO3.settings.ajaxUrls.newsletter_update_recipient);
        Messenger.modal = Modal.advanced({
          type: Modal.types.ajax,
          title: 'Update recipient',
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
              text: 'Update recipient',
              btnClass: 'btn btn-primary',
              trigger: function () {
                // get modal content before submit
                $('.btn', Messenger.modal).attr('disabled', 'disabled');
                const textarea = document.querySelector('.modal-body .recipient textarea');
                const updateRecipient = Messenger.getEditRecipientUrl(
                  TYPO3.settings.ajaxUrls.newsletter_update_recipient_save,
                  textarea.value,
                );

                // Ajax request
                $.ajax({
                  url: updateRecipient,
                  data: textarea.value,
                  type: 'POST',

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
  Newsletter.initialize();
  return Newsletter;
});
