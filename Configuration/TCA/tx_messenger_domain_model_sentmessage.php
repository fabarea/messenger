<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sent_message',
        'label' => 'subject',
        'default_sortby' => 'ORDER BY uid DESC',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'content,',
        'typeicon_classes' => [
            'default' => 'extensions-messenger-sentmessage',
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'sender, recipient, subject, body, sent_time, attachment, was_opened, context'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'sender' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sender',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'recipient' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'subject' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:subject',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'body' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:body',
            'config' => [
                'type' => 'text',
                'rows' => 4,
                'cols' => 50,
                'readOnly' => TRUE,
            ],
        ],
        'sent_time' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sent_time',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'time',
                'checkbox' => 1,
                'readOnly' => TRUE,
                'default' => time(),
            ],
        ],
        'context' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:context',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'attachment' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:attachment',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'was_opened' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:was_opened',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
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
                'renderer' => new \Fab\Vidi\Grid\CheckBoxRenderer(),
            ],
            'uid' => [
                'visible' => FALSE,
                'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:uid',
                'width' => '5px',
            ],
            'sender' => [],
            'recipient' => [],
            'subject' => [],
            'body' => [
                'width' => '500px',
                'sortable' => FALSE,
            ],
            'sent_time' => [
                'format' => 'datetime',
                'width' => '150px',
            ],
            'attachment' => [
                'visible' => FALSE,
            ],
            'was_opened' => [
                'visible' => FALSE,
            ],
            'context' => [
                'width' => '100px',
            ],
            '__buttons' => [
                'renderer' => new \Fab\Vidi\Grid\ButtonGroupRenderer(),
            ],
        ],
    ],
];
