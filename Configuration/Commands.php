<?php
/**
 * example: bin/typo3 log:cleanUp
 */
return [
    'messenger:dequeue' => [
        'class' => \Fab\Messenger\Command\MessageQueueCommandController::class
    ],
    'messenger:cleanUp' => [
        'class' => \Fab\Messenger\Command\LogCommandController::class
    ],
];
