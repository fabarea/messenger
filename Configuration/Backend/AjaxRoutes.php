<?php

use Fab\Messenger\Controller\Ajax\AjaxExportDataController;
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
        'target' => AjaxExportDataController::class . '::confirmAction',
    ],
];
