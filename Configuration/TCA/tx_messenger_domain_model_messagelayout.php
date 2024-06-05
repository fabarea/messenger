<?php

use Fab\Vidi\Grid\ButtonGroupRenderer;
use Fab\Vidi\Grid\CheckBoxRenderer;

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' =>
            'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:message_layout',
        'label' => 'qualifier',
        'default_sortby' => 'ORDER BY qualifier ASC',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'qualifier, content,',
        'typeicon_classes' => [
            'default' => 'extensions-messenger-messagelayout',
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_diffsource, hidden;;1, qualifier, content'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [['', 0]],
                'foreign_table' => 'tx_messenger_domain_model_messagelayout',
                'foreign_table_where' =>
                    'AND tx_messenger_domain_model_messagelayout.pid=###CURRENT_PID### AND tx_messenger_domain_model_messagelayout.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'qualifier' => [
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:qualifier',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim,unique',
            ],
        ],
        'content' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:content',
            'config' => [
                'type' => 'text',
                'eval' => 'trim',
                'default' => 'Header to be replaced...

{BODY}

Footer to be replaced...
',
            ],
        ],
    ],
    'grid' => [
        'facets' => ['uid', 'qualifier'],
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
            'qualifier' => [
                'editable' => true,
            ],
            'content' => [],
            '__buttons' => [
                'renderer' => ButtonGroupRenderer::class,
            ],
        ],
    ],
];
