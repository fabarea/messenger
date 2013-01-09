<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_messenger_domain_model_sentmessage'] = array(
	'ctrl' => $TCA['tx_messenger_domain_model_sentmessage']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden, user, message, content, sent_time',
	),
	'types' => array(
		'1' => array('showitem' => 'user, message, content, sent_time'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'user' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.user',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'readOnly' => TRUE,
				'eval' => 'int',
			),
		),
		'message' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.message',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'readOnly' => TRUE,
				'eval' => 'int',
			),
		),
		'content' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.content',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'sent_time' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.sent_time',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'time',
				'checkbox' => 1,
				'readOnly' => TRUE,
				'default' => time(),
			),
		),
	),
);

?>