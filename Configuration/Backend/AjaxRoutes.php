<?php

use Fab\Messenger\Controller\Ajax\SendAgainConfirmationAjaxController;

return [
    'send_again_confirmation' => [
        'path' => '/messenger/send-again/confirmation',
        'target' => SendAgainConfirmationAjaxController::class . '::confirmAction',
    ],
];
