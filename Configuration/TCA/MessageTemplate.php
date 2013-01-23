<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_messenger_domain_model_messagetemplate'] = array(
	'ctrl' => $TCA['tx_messenger_domain_model_messagetemplate']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_diffsource, hidden, identifier, subject, body',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_diffsource, hidden;;1, identifier, subject, body'),
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
		'hidden' => array(
			'exclude' => 1,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'identifier' => array(
			'exclude' => 0,
			'l10n_mode' => 'exclude',
			'l10n_display' => 'defaultAsReadonly',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_messagetemplate.identifier',
			'config' => array(
				'type' => 'input',
				'size' => 100,
				'eval' => 'trim,required,unique'
			),
		),
		'subject' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_messagetemplate.subject',
			'config' => array(
				'type' => 'input',
				'size' => 100,
				'eval' => 'trim,required'
			),
		),
		'body' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_messagetemplate.body',
			'config' => array(
				'type' => 'text',
				'eval' => 'trim'
			),
		),
	),
);

?>