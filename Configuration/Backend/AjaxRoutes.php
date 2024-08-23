<?php
return [
    'send_again_confirmation' => [
        'path' => '/messenger/send-again/confirmation',
        'target' => \Fab\Messenger\Controller\MessageSentController::class . '::confirmAction',
    ],
];
