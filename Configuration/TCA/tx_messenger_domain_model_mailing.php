<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_mailing.xlf:mailing',
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
        '1' => ['showitem' => 'title, comment, sent_time'],
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
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_mailing.xlf:title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'comment' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_mailing.xlf:comment',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => TRUE,
                'eval' => 'trim',
            ],
        ],
        'sent_time' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_mailing.xlf:sent_time',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'time',
                'checkbox' => 1,
                'readOnly' => TRUE,
                'default' => time(),
            ],
        ],
    ],
    'grid' => [
        'facets' => [
            'uid',
            'title',
        ],
        'columns' => [
            '__checkbox' => [
                'renderer' => new \Fab\Vidi\Grid\CheckBoxComponent(),
            ],
            'uid' => [
                'visible' => FALSE,
                'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:uid',
                'width' => '5px',
            ],
            'title' => [],
            '__buttons' => [
                'renderer' => new \Fab\Vidi\Grid\ButtonGroupComponent(),
            ],
        ],
    ],
];
