<?php

use Fab\Messenger\Controller\Ajax\ExportDataAjaxController;
use Fab\Messenger\Controller\Ajax\SendAgainConfirmationAjaxController;
use Fab\Messenger\Controller\Ajax\SendMessageController;
use Fab\Messenger\Controller\Ajax\UpdateRecipientController;

return [
    'messenger_send_again_confirmation' => [
        'path' => '/messenger/send-again/confirmation',
        'target' => SendAgainConfirmationAjaxController::class . '::confirmAction',
    ],
    'messenger_send_again' => [
        'path' => '/messenger/send-again',
        'target' => SendAgainConfirmationAjaxController::class . '::sendAgainAction',
    ],
    'messenger_export_data' => [
        'path' => '/messenger/export-data',
        'target' => ExportDataAjaxController::class . '::confirmAction',
    ],

    'messenger_export_data_validation' => [
        'path' => '/messenger/export-data/validate',
        'target' => ExportDataAjaxController::class . '::validateAction',
    ],

    'newsletter_update_recipient' => [
        'path' => '/newsletter/update-recipient',
        'target' => UpdateRecipientController::class . '::editAction',
    ],

    'newsletter_update_recipient_save' => [
        'path' => '/newsletter/update-recipient/save',
        'target' => UpdateRecipientController::class . '::saveAction',
    ],

    'newsletter_send_message_from_clipboard' => [
        'path' => '/newsletter/send-message-from-clipboard', // display-send-message-modal
        'target' => SendMessageController::class . '::messageFromRecipientAction', // DisplaySendMessageModalAjaxController::class . '::displayAction',
    ],
    'newsletter_send_message_from_enqueue' => [
        'path' => '/newsletter/send-message-from-enqueue', // enqueue or enqueue-messages
        'target' => SendMessageController::class . '::enqueueAction', // EnqueueMessageAjaxController::class . '::enqueueAction',
    ],
];
