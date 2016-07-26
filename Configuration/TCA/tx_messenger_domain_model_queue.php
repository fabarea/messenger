<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.qeue',
        'label' => 'user',
        'default_sortby' => 'ORDER BY uid ASC',
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
            'default' => 'extensions-messenger-queue',
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'sender, recipient, subject, body, attachment, context'],
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
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.sender',
            'config' => [
                'type' => 'text',
                'rows' => 4,
                'cols' => 50,
                'readOnly' => TRUE,
            ],
        ],
        'recipient' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.recipient',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'subject' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.subject',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'body' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.body',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'context' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.context',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'attachment' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf.attachment',
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
            ],
            'attachment' => [
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
