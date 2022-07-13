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

            return uri.toString();
        },

        /**
         * @return void
         */
        initialize: function() {

            // Add listener on bulk send button
            $(document).on('click', '.btn-bulk-send', function(e) {

                e.preventDefault();

                var me = this;
                var url = Messenger.getEditStorageUrl($(this).attr('href'));

                Vidi.modal = Modal.advanced({
                    type: Modal.types.ajax,
                    title: TYPO3.l10n.localize('message.send'),
                    severity: top.TYPO3.Severity.notice,
                    content: decodeURIComponent(url),
                    buttons: [
                        {
                            text: TYPO3.l10n.localize('close'),
                            btnClass: 'btn btn-primary',
                            trigger: function() {
                                Modal.dismiss();
                            }
                        }
                    ],
                    ajaxCallback: function() {

                        // format modal title.
                        var modalTitle = $('.modal-title', Vidi.modal).html() + ' (' + $('#numberOfRecipients', Vidi.modal).html() + ')';
                        $('.modal-title', Vidi.modal).html(modalTitle);

                        /**
                         * Delete a selection in the form just opened in the popup.
                         */
                        $(Vidi.modal).on('submit', '#form-bulk-send', function(e) {

                            // Stop default behaviour.
                            e.preventDefault();


                            $('.modal-body', Vidi.modal).css('opacity', 0.6);
                            $('#btn-bulk-send', Vidi.modal).attr('disabled', 'disabled');

                            // If body contains a numerical corresponding to a page id, the page will be rendered.
                            if (!$('#has-body-text', Vidi.modal).is(':checked') && $('#messenger-page-id', Vidi.modal).val()) {
                                $('#messenger-body', Vidi.modal).val($('#messenger-page-id', Vidi.modal).val());
                            }

                            var $form = $(this).closest('form');

                            // Ajax request
                            $.ajax({
                                url: $($form).attr('action'),
                                data: $form.serialize(),
                                method: 'post',

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
                        });

                        /**
                         * Delete a selection in the form just opened in the popup.
                         */
                        $(Vidi.modal).on('submit', '#form-send-test', function(e) {

                            // Stop default behaviour.
                            e.preventDefault();

                            $('.modal-body', Vidi.modal).css('opacity', 0.6);
                            $('#btn-send-test', Vidi.modal).attr('disabled', 'disabled');

                            // If body contains a numerical corresponding to a page id, the page will be rendered.
                            //if (!$('#has-body-text', Vidi.modal).is(':checked') && $('#messenger-page-id', Vidi.modal).val()) {
                            //    $('#messenger-body', Vidi.modal).val($('#messenger-page-id', Vidi.modal).val());
                            //}

                            $('#messenger-sender-test', Vidi.modal).val($('#messenger-sender', Vidi.modal).val());
                            $('#messenger-subject-test', Vidi.modal).val($('#messenger-subject', Vidi.modal).val());
                            $('#messenger-body-test', Vidi.modal).val($('#messenger-body', Vidi.modal).val());

                            var $form = $(this).closest('form');

                            // Ajax request
                            $.ajax({
                                url: $($form).attr('action'),
                                data: $form.serialize(),
                                method: 'post',

                                /**
                                 * On success call back
                                 * @param response
                                 */
                                success: function(response) {
                                    $('.modal-body', Vidi.modal).css('opacity', 1);
                                    $('#btn-send-test', Vidi.modal).removeAttr('disabled');

                                    Notification.success(response);
                                }
                            });
                        });
                    }
                })

            });

        }

    };

    Messenger.initialize();
    return Messenger;
});
