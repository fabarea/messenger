<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:qeue',
        'label' => 'sender',
        'default_sortby' => 'ORDER BY scheduled_distribution_time ASC',
        'crdate' => 'crdate',
        'searchFields' => 'subject, body, mailing_name, uuid',
        'typeicon_classes' => [
            'default' => 'extensions-messenger-queue',
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'sender, recipient, subject, body, attachment, context, mailing_name, uuid, scheduled_distribution_time, message_template, message_layout, redirect_email_from'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'sender' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sender',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'recipient' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'recipient_cc' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient_cc',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'recipient_bcc' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient_bcc',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'message_serialized' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.message_serialized',
            'config' => [
                'type' => 'text',
                'rows' => 4,
                'cols' => 50,
                'readOnly' => true,
            ],
        ],
        'redirect_email_from' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:redirect_email_from',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'subject' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:subject',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'body' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:body',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
            ],
        ],
        'context' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:context',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'attachment' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:attachment',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'mailing_name' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:mailing_name',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
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
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:scheduled_distribution_time',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'datetime',
            ],
        ],
        'message_template' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:message_template',
            'config' => [
                'type' => 'select',
                'table_foreign' => 'tx_messenger_domain_model_messagetemplate',
                'minitems' => 0,
                'maxitems' => 1,
                'readOnly' => true,
            ],
        ],
        'message_layout' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:message_layout',
            'config' => [
                'type' => 'select',
                'table_foreign' => 'tx_messenger_domain_model_messagelayout',
                'minitems' => 0,
                'maxitems' => 1,
                'readOnly' => true,
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
        'facets' => [
            'uid',
            'subject',
        ],
        'columns' => [
            '__checkbox' => [
                'renderer' => \Fab\Vidi\Grid\CheckBoxRenderer::class,
            ],
            'uid' => [
                'visible' => false,
                'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:uid',
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
                'renderer' => \Fab\Messenger\Grid\UuidRenderer::class,
                'rendererConfiguration' => [
                    'source' => 'queue'
                ]
            ],
            'attachment' => [
                'visible' => false,
            ],
            'context' => [
                'width' => '100px',
                'visible' => false,
            ],
            '__buttons' => [
                'renderer' => \Fab\Vidi\Grid\ButtonGroupRenderer::class,
            ],
        ],
    ],
];
