<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

return array(
	'ctrl' => array(
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
		'enablecolumns' => array(
			'disabled' => 'hidden'
		),
		'searchFields' => 'qualifier, content,',
		'typeicon_classes' => array(
			'default' => 'extensions-messenger-messagelayout',
		),
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_diffsource, hidden, qualifier, content',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_diffsource, hidden;;1, qualifier, content'),
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
				'foreign_table' => 'tx_messenger_domain_model_messagelayout',
				'foreign_table_where' => 'AND tx_messenger_domain_model_messagelayout.pid=###CURRENT_PID### AND tx_messenger_domain_model_messagelayout.sys_language_uid IN (-1,0)',
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
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:qualifier',
			'config' => array(
				'type' => 'input',
				'size' => 100,
				'eval' => 'trim,unique'
			),
		),
		'content' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf:content',
			'config' => array(
				'type' => 'text',
				'eval' => 'trim',
				'default' => 'Header to be replaced...

{BODY}

Footer to be replaced...
'
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
			),
			'content' => array(),
			'__buttons' => array(
				'renderer' => new \TYPO3\CMS\Vidi\Grid\ButtonGroupComponent(),
			),
		),
	),
);
