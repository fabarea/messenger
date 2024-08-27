<?php

use Fab\Messenger\Controller\Ajax\SendAgainConfirmationAjaxController;

return [
    'send_again_confirmation' => [
        'path' => '/messenger/send-again/confirmation',
        'target' => SendAgainConfirmationAjaxController::class . '::confirmAction',
    ],
    'send_again' => [
        'path' => '/messenger/send-again',
        'target' => SendAgainConfirmationAjaxController::class . '::sendAgainAction',
    ],
];
