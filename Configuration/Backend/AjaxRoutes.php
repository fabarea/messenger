<?php

use Fab\Messenger\Controller\Ajax\DisplaySendMessageModalAjaxController;
use Fab\Messenger\Controller\Ajax\EnqueueMessageAjaxController;
use Fab\Messenger\Controller\Ajax\ExportDataAjaxController;
use Fab\Messenger\Controller\Ajax\SendAgainConfirmationAjaxController;
use Fab\Messenger\Controller\Ajax\UpdateRecipientAjaxController;

return [
    'messenger_send_again_confirmation' => [
        'path' => '/messenger/send-again/confirmation',
        'target' => SendAgainConfirmationAjaxController::class . '::confirmAction',
    ],
    'messenger_send_again' => [
        'path' => '/messenger/send-again',
        'target' => SendAgainConfirmationAjaxController::class . '::sendAgainAction',
    ],
    'messenger_export_data_confirm' => [
        'path' => '/messenger/export-data/confirm',
        'target' => ExportDataAjaxController::class . '::confirmAction',
    ],

    'messenger_export_data_export' => [
        'path' => '/messenger/export-data/export',
        'target' => ExportDataAjaxController::class . '::exportAction',
    ],

    'newsletter_update_recipient' => [
        'path' => '/newsletter/update-recipient',
        'target' => UpdateRecipientAjaxController::class . '::editAction',
    ],

    'newsletter_update_recipient_save' => [
        'path' => '/newsletter/update-recipient/save',
        'target' => UpdateRecipientAjaxController::class . '::saveAction',
    ],

    'newsletter_display_send_message_modal' => [
        'path' => '/newsletter/display-send-message-modal',
        'target' => DisplaySendMessageModalAjaxController::class . '::displayAction',
    ],
    'newsletter_enqueue_messages' => [
        'path' => '/newsletter/enqueue-messages',
        'target' => EnqueueMessageAjaxController::class . '::enqueueAction',
    ],
    'newsletter_send_test_messages' => [
        'path' => '/newsletter/send-test-messages',
        'target' => EnqueueMessageAjaxController::class . '::sendTestAction',
    ],
];
