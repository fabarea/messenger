<?php

use Fab\Messenger\Grid\UuidRenderer;
use Fab\Vidi\Grid\ButtonGroupRenderer;
use Fab\Vidi\Grid\CheckBoxRenderer;

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf:qeue',
        'label' => 'sender',
        'default_sortby' => 'ORDER BY scheduled_distribution_time ASC',
        'crdate' => 'crdate',
        'searchFields' => 'subject, body, mailing_name, uuid',
        'typeicon_classes' => [
            'default' => 'extensions-messenger-queue',
        ],

        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'rootLevel' => -1,
    ],
    'types' => [
        '1' => [
            'showitem' =>
                'sender, recipient, subject, body, attachment, context, mailing_name, uuid, scheduled_distribution_time, message_template, message_layout, redirect_email_from',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'sender' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sender',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'recipient' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'recipient_cc' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient_cc',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'recipient_bcc' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient_bcc',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'message_serialized' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.message_serialized',
            'config' => [
                'type' => 'text',
                'rows' => 4,
                'cols' => 50,
                'readOnly' => false,
            ],
        ],
        'redirect_email_from' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:redirect_email_from',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'subject' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:subject',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'body' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:body',
            'config' => [
                'type' => 'text',
                'readOnly' => false,
            ],
        ],
        'context' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:context',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'attachment' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:attachment',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'mailing_name' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:mailing_name',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
            ],
        ],
        'uuid' => [
            'label' => 'UUID',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'scheduled_distribution_time' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:scheduled_distribution_time',
            'config' => [
                'type' => 'input',
                'readOnly' => false,
                'eval' => 'datetime',
            ],
        ],
        'message_template' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:message_template',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'table_foreign' => 'tx_messenger_domain_model_messagetemplate',
                'items' => [['', 0]],
                'minitems' => 0,
                'readOnly' => false,
            ],
        ],
        'message_layout' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:message_layout',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'table_foreign' => 'tx_messenger_domain_model_messagelayout',
                'minitems' => 0,
                'items' => [['', 0]],
                'readOnly' => false,
            ],
        ],
        'ip' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:ip',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
            ],
        ],
    ],
    'grid' => [
        'facets' => ['uid', 'subject'],
        'columns' => [
            '__checkbox' => [
                'renderer' => CheckBoxRenderer::class,
            ],
            'uid' => [
                'visible' => false,
                'label' =>
                    'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:uid',
                'width' => '5px',
            ],
            'sender' => [],
            'recipient' => [],
            'subject' => [],
            'body' => [
                'width' => '500px',
                'sortable' => false,
                'visible' => false,
            ],
            'scheduled_distribution_time' => [
                'format' => \Fab\Vidi\Formatter\Datetime::class,
            ],
            'mailing_name' => [],
            'uuid' => [
                'renderer' => UuidRenderer::class,
                'rendererConfiguration' => [
                    'source' => 'queue',
                ],
            ],
            'attachment' => [
                'visible' => false,
            ],
            'context' => [
                'width' => '100px',
                'visible' => false,
            ],
            '__buttons' => [
                'renderer' => ButtonGroupRenderer::class,
            ],
        ],
    ],
];
