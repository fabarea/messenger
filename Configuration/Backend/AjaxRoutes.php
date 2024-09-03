<?php

use Fab\Messenger\Controller\Ajax\ExportDataAjaxController;
use Fab\Messenger\Controller\Ajax\SendAgainConfirmationAjaxController;

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
];
