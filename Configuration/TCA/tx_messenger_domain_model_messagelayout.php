<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:message_layout',
        'label' => 'qualifier',
        'default_sortby' => 'ORDER BY qualifier ASC',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,

        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'searchFields' => 'qualifier, content,',
        'typeicon_classes' => [
            'default' => 'extensions-messenger-messagelayout',
        ],
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_diffsource, hidden, qualifier, content',
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_diffsource, hidden;;1, qualifier, content'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'sys_language_uid' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_messenger_domain_model_messagelayout',
                'foreign_table_where' => 'AND tx_messenger_domain_model_messagelayout.pid=###CURRENT_PID### AND tx_messenger_domain_model_messagelayout.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ]
        ],
        'hidden' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'qualifier' => [
            'exclude' => 0,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:qualifier',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim,unique'
            ],
        ],
        'content' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:content',
            'config' => [
                'type' => 'text',
                'eval' => 'trim',
                'default' => 'Header to be replaced...

{BODY}

Footer to be replaced...
'
            ],
        ],
    ],
    'grid' => [
        'facets' => [
            'uid',
            'qualifier',
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
            'qualifier' => [
                'editable' => TRUE,
            ],
            'content' => [],
            '__buttons' => [
                'renderer' => new \Fab\Vidi\Grid\ButtonGroupRenderer(),
            ],
        ],
    ],
];
