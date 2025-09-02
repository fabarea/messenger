<?php

declare(strict_types=1);

use Fab\Messenger\Controller\MessageLayoutController;
use Fab\Messenger\Controller\MessageQueueController;
use Fab\Messenger\Controller\MessageTemplateController;
use Fab\Messenger\Controller\RecipientModuleController;
use Fab\Messenger\Controller\SentMessageModuleController;

return [

    'messenger' => [
        'parent' => '',
        'position' => ['before' => 'user'],
        'access' => 'group,user',
        'workspaces' => 'live',
        'path' => '/module/messenger',
        'iconIdentifier' => 'extensions-messenger-module',
        'labels' => 'LLL:EXT:messenger/Resources/Private/Language/module_messenger.xlf',
    ],

    'messenger_tx_messenger_m2' => [
        'parent' => 'messenger',
        'position' => ['after' => '*'],
        'access' => 'admin',
        'workspaces' => 'live',
        'path' => '/module/messenger/tx_messenger_m2',
        'iconIdentifier' => 'extensions-messenger-messagetemplate',
        'labels' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf',
        'extensionName' => 'Fab.Messenger',
        'controllerActions' => [
            MessageTemplateController::class => [
                'index'
            ],
        ],
    ],

    'messenger_tx_messenger_m3' => [
        'parent' => 'messenger',
        'position' => ['after' => '*'],
        'access' => 'admin',
        'workspaces' => 'live',
        'path' => '/module/messenger/tx_messenger_m3',
        'iconIdentifier' => 'extensions-messenger-messagelayout',
        'labels' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf',
        'extensionName' => 'Fab.Messenger',
        'controllerActions' => [
            MessageLayoutController::class => [
                'index'
            ],
        ],
    ],

    'messenger_tx_messenger_m1' => [
        'parent' => 'messenger',
        'position' => ['after' => '*'],
        'access' => 'admin',
        'workspaces' => 'live',
        'path' => '/module/messenger/tx_messenger_m1',
        'iconIdentifier' => 'extensions-messenger-sentmessage',
        'labels' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf',
        'extensionName' => 'Fab.Messenger',
        'controllerActions' => [
            SentMessageModuleController::class => [
                'index'
            ],
        ],
    ],

    'messenger_tx_messenger_m4' => [
        'parent' => 'messenger',
        'position' => ['after' => '*'],
        'access' => 'admin',
        'workspaces' => 'live',
        'path' => '/module/messenger/tx_messenger_m4',
        'iconIdentifier' => 'extensions-messenger-queue',
        'labels' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf',
        'extensionName' => 'Fab.Messenger',
        'controllerActions' => [
            MessageQueueController::class => [
                'index'
            ],
        ],
    ],

    'web_tx_messenger_m5' => [
        'parent' => 'web',
        'position' => ['after' => '*'],
        'access' => 'admin',
        'workspaces' => 'live',
        'path' => '/module/web/tx_messenger_m5',
        'iconIdentifier' => 'extensions-messenger-sentmessage',
        'labels' => 'LLL:EXT:messenger/Resources/Private/Language/module_newsletter.xlf',
        'extensionName' => 'Fab.Messenger',
        'controllerActions' => [
            RecipientModuleController::class => [
                'index'
            ],
        ],
    ],
];
