<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

$table = 'tx_messenger_domain_model_messagetemplate';

return [
    'ctrl' => [
        'title' =>
            'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:message_template',
        'label' => 'subject',
        'label_alt' => 'qualifier',
        'label_alt_force' => 1,
        'default_sortby' => 'ORDER BY qualifier ASC',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'requestUpdate' => 'type',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'qualifier, subject, body',
        'type' => 'type',
        'typeicon_classes' => [
            'default' => 'extensions-messenger-messagetemplate',
        ],
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'rootLevel' => -1,
    ],
    'types' => [
        '1' => [
            'showitem' => 'type, sys_language_uid, l10n_diffsource, hidden, qualifier, subject, body, message_layout',
        ],
        '2' => [
            'showitem' =>
                'type, sys_language_uid, l10n_diffsource, hidden, qualifier, subject, source_page, message_layout',
        ],
        '3' => [
            'showitem' =>
                'type, sys_language_uid, l10n_diffsource, hidden, qualifier, subject, source_file, template_engine, message_layout',
        ],
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
                'foreign_table' => 'tx_messenger_domain_model_messagetemplate',
                'foreign_table_where' =>
                    'AND tx_messenger_domain_model_messagetemplate.pid=###CURRENT_PID### AND tx_messenger_domain_model_messagetemplate.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'type' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:type.content_from_text',
                        1,
                    ],
                    [
                        'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:type.content_from_page',
                        2,
                    ],
                    [
                        'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:type.content_from_file',
                        3,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
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
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:qualifier',
            'config' => [
                'type' => 'input',
                'placeholder' =>
                    'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:qualifier.placeholder',
                'size' => 100,
                'eval' => 'trim,unique',
            ],
        ],
        'subject' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:subject',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim,required',
            ],
        ],
        'source_file' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:source_file',
            'config' => [
                'type' => 'input',
                'default' => 'EXT:foo/Resources/Private/Message/Contact.html',
                'size' => 100,
                'eval' => 'trim,required',
            ],
        ],
        'source_page' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:source_page',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => '1',
                'minitems' => '0',
                'maxitems' => '1',
                'wizards' => [
                    'suggest' => [
                        'type' => 'suggest',
                    ],
                ],
            ],
        ],
        'template_engine' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:template_engine',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:template_engine.both',
                        'both',
                    ],
                    [
                        'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:template_engine.fluid',
                        'fluid',
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'body' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:body',
            'config' => [
                'type' => 'text',

                'eval' => 'trim',
                'default' => 'Hello Admin,

A new submission was made by {email}.

You can write Markdown and Fluid View Helper within your template.
Markers such as {foo} have to be posted by Messenger.

**MarkDown**

* bullet list
* bullet list

**Fluid View Helper**

<f:translate key="foo" extensionName="ext"/>: {foo}

<f:link.page pageUid="1" absolute="1">Open page</f:link.page>

**Messenger View Helper**

Show detail of an item:

{namespace m=Fab\Messenger\ViewHelpers}
			',
            ],
        ],
        'message_layout' => [
            'label' =>
                'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:message_layout',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'allowNonIdValues' => true,
                'items' => [['', 0]],
                'foreign_table' => 'tx_messenger_domain_model_messagelayout',
                'size' => 1,
                'default' => '',
                'minitems' => 0,
            ],
        ],
    ],
];
