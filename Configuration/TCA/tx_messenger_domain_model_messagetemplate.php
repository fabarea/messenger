<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

return array(
	'ctrl' => array(
		'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:message_template',
		'label' => 'subject',
		'label_alt' => 'qualifier',
		'label_alt_force' => 1,
		'default_sortby' => 'ORDER BY qualifier ASC',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'requestUpdate' => 'type',

		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden'
		),
		'searchFields' => 'qualifier, subject,body,',
		'type' => 'type',
		'typeicon_classes' => array(
			'default' => 'extensions-messenger-messagetemplate',
		),
	),
	'types' => array(
		'1' => array('showitem' => 'type, sys_language_uid, l10n_diffsource, hidden, qualifier, subject, body, message_layout'),
		'2' => array('showitem' => 'type, sys_language_uid, l10n_diffsource, hidden, qualifier, subject, source_page, message_layout'),
		'3' => array('showitem' => 'type, sys_language_uid, l10n_diffsource, hidden, qualifier, subject, source_file, template_engine, message_layout'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_messenger_domain_model_messagetemplate',
				'foreign_table_where' => 'AND tx_messenger_domain_model_messagetemplate.pid=###CURRENT_PID### AND tx_messenger_domain_model_messagetemplate.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'type' => array(
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:type',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:type.content_from_text', 1),
					array('LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:type.content_from_page', 2),
					array('LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:type.content_from_file', 3),
				),
				'size' => 1,
				'maxitems' => 1,
			),
		),
		'hidden' => array(
			'exclude' => 1,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'qualifier' => array(
			'exclude' => 0,
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:qualifier',
			'config' => array(
				'type' => 'input',
				'placeholder' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:qualifier.placeholder',
				'size' => 100,
				'eval' => 'trim,unique',
			),
		),
		'subject' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:subject',
			'config' => array(
				'type' => 'input',
				'size' => 100,
				'eval' => 'trim,required',
			),
		),
		'source_file' => array(
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:source_file',
			'config' => array(
				'type' => 'input',
				'default' => 'EXT:foo/Resources/Private/Message/Contact.html',
				'size' => 100,
				'eval' => 'trim,required',
			),
		),
		'source_page' => array(
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:source_page',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'minitems' => '0',
				'maxitems' => '1',
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest',
					),
				),
			),
		),
		'template_engine' => array(
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:template_engine',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:template_engine.both', 'both'),
					array('LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:template_engine.fluid', 'fluid'),
				),
				'size' => 1,
				'maxitems' => 1,
			)
		),
		'body' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:body',
			'config' => array(
				'type' => 'text',
				'rows' => 10,
				'cols' => 5,
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

<m:widget.show item="markerName" dataType="tx_ext_foo"/>

{namespace m=Fab\Messenger\ViewHelpers}
			'),
		),
		'message_layout' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:message_layout',
			'config' => array(
				'type' => 'select',
				'items' => array(
					0 => array(),
				),
				'foreign_table' => 'tx_messenger_domain_model_messagelayout',
				'size' => 1,
				'minitems' => 0,
			),
		),
	),
	'grid' => array(
		'facets' => array(
			'uid',
			'qualifier',
		),
		'columns' => array(
			'__checkbox' => array(
				'renderer' => new \TYPO3\CMS\Vidi\Grid\CheckBoxComponent(),
			),
			'uid' => array(
				'visible' => FALSE,
				'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:uid',
				'width' => '5px',
			),
			'qualifier' => array(
				'editable' => TRUE,
				'width' => '150px',
			),
			'subject' => array(
				'editable' => TRUE,
			),
//			'message_layout' => array(
//				'visible' => FALSE,
//			),
			'body' => array(),
			'__buttons' => array(
				'renderer' => new \TYPO3\CMS\Vidi\Grid\ButtonGroupComponent(),
			),
		),
	),
);
