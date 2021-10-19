<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sent_message',
        'label' => 'sender',
        'default_sortby' => 'ORDER BY sent_time DESC',
        'crdate' => 'crdate',
        'searchFields' => 'subject, recipient, body, mailing_name, uid',
        'typeicon_classes' => [
            'default' => 'extensions-messenger-sentmessage',
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'sender, recipient, subject, body, attachment, context, mailing_name, uuid, scheduled_distribution_time, message_template, message_layout, sent_time, redirect_email_from'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'sender' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sender',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
                'eval' => 'trim',
            ],
        ],
        'recipient' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
                'eval' => 'trim',
            ],
        ],
        'recipient_cc' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient_cc',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
            ],
        ],
        'recipient_bcc' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient_bcc',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
            ],
        ],
        'redirect_email_from' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:redirect_email_from',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
            ],
        ],
        'subject' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:subject',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
                'eval' => 'trim',
            ],
        ],
        'body' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:body',
            'config' => [
                'type' => 'text',
                'rows' => 4,
                'cols' => 50,
                'readOnly' => true,
            ],
        ],
        'context' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:context',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
                'eval' => 'trim',
            ],
        ],
        'attachment' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:attachment',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
                'eval' => 'trim',
            ],
        ],
        'mailing_name' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:mailing_name',
            'config' => [
                'type' => 'input',
                'size' => 50,
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
                'size' => 50,
                'readOnly' => true,
                'eval' => 'datetime',
            ],
        ],
        'message_template' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:message_template',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'table_foreign' => 'tx_messenger_domain_model_messagetemplate',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'readOnly' => true,
            ],
        ],
        'message_layout' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:message_layout',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'table_foreign' => 'tx_messenger_domain_model_messagelayout',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'readOnly' => true,
            ],
        ],
        'ip' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:ip',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
                'eval' => 'trim',
            ],
        ],
        'sent_time' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sent_time',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'time',
                'checkbox' => 1,
                'readOnly' => true,
            ],
        ],
        'was_opened' => [
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:was_opened',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => true,
                'eval' => 'trim',
            ],
        ],
    ],
    'grid' => [
        'facets' => [
            'uid',
            'subject',
            'recipient',
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
            'sent_time' => [
                'format' => \Fab\Vidi\Formatter\Datetime::class,
                'width' => '150px',
            ],
            'mailing_name' => [],
            'uuid' => [
                'renderer' => \Fab\Messenger\Grid\UuidRenderer::class,
            ],
            'attachment' => [
                'visible' => false,
            ],
            'was_opened' => [
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
