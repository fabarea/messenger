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
define([
    'jquery',
    'TYPO3/CMS/Backend/Modal',
    'TYPO3/CMS/Backend/Notification'
], function($, Modal, Notification) {

    'use strict';

    var Messenger = {

        /**
         * Get edit storage URL.
         *
         * @param {string} url
         * @return string
         * @private
         */
        getEditStorageUrl: function(url) {

            var uri = new Uri(url);

            if (Vidi.Grid.hasSelectedRows()) {
                // Case 1: mass editing for selected rows.

                // Add parameters to the Uri object.
                uri.addQueryParam('tx_messenger_user_messengerm1[matches][uid]', Vidi.Grid.getSelectedIdentifiers().join(','));

            } else {

                var storedParameters = Vidi.Grid.getStoredParameters();

                if (typeof storedParameters === 'object') {

                    if (storedParameters.search) {
                        uri.addQueryParam('search[value]', storedParameters.search.value);
                    }

                    if (storedParameters.order) {
                        uri.addQueryParam('order[0][column]', storedParameters.order[0].column);
                        uri.addQueryParam('order[0][dir]', storedParameters.order[0].dir);
                    }
                }

            }

            return decodeURIComponent(uri.toString());
        },

        /**
         * @return void
         */
        initialize: function() {

            // Add listener on bulk send button
            $(document).on('click', '.btn-sendAgain', function(e) {

                e.preventDefault();

                var me = this;
                var url = Messenger.getEditStorageUrl($(this).attr('href'));

                Vidi.modal = Modal.advanced({
                    type: Modal.types.ajax,
                    title: TYPO3.l10n.localize('message.send'),
                    severity: top.TYPO3.Severity.notice,
                    content: url,
                    buttons: [
                        {
                            text: TYPO3.l10n.localize('active.1'),
                            btnClass: 'btn btn-default',
                            trigger: function() {
                                Modal.dismiss();
                            }
                        },
                        {
                            text: TYPO3.l10n.localize('active.0'),
                            btnClass: 'btn btn-primary',
                            trigger: function() {

                                // Disable button
                                $('.btn', Vidi.modal).attr('disabled', 'disabled');

                                // Generate the dequeue URL
                                var sendAgainUrl = url.replace('confirm&', 'sendAgain&');

                                    // Ajax request
                                    $.ajax({
                                        url: sendAgainUrl,

                                        /**
                                         * On success call back
                                         *
                                         * @param response
                                         */
                                        success: function(response) {
                                            Vidi.grid.fnDraw(false); // false = for keeping the pagination.
                                            Notification.success('', response);
                                            Modal.dismiss();
                                        }
                                    });
                            }
                        }
                    ],
                })

            });

        }

    };

    Messenger.initialize();
    return Messenger;
});
