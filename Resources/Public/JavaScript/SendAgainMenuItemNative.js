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

/**
 * Module: Fab/Messenger/Media
 */
define(['jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Notification'], function ($, Modal, Notification) {
  'use strict';

  var Messenger = {
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
      var columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);

      // Add listener on bulk send button
      $(document).on('click', '.btn-sendAgain', function (e) {
        e.preventDefault();

        let columnsToSend = [...document.querySelectorAll('.select:checked')].map((element) => element.value);
        let dataCount = columnsToSend.length;
        var url = Messenger.getEditStorageUrl($(this).attr('href'));

        //TYPO3.l10n.localize('message.send')
        console.log(TYPO3.l10n.localize);
        console.log(TYPO3.l10n.localize('message.send'));

        return;
        top.TYPO3.Modal.confirm(
          'Send',
          'Are you sure to send  ' + dataCount + ' messages  ?',
          top.TYPO3.Severity.warning,
        )
          .on('confirm.button.ok', function () {
            window.location.href = url;
            top.TYPO3.Modal.currentModal.trigger('modal-dismiss');
          })
          .on('confirm.button.cancel', function () {
            top.TYPO3.Modal.currentModal.trigger('modal-dismiss');
          });
      });
    },
  };

  Messenger.initialize();
  return Messenger;
});
