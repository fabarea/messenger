<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

return array(
	'ctrl' => array(
		'title' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage',
		'label' => 'user',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'searchFields' => 'content,',
		'typeicon_classes' => array(
			'default' => 'extensions-messenger-sentmessage',
		),
	),
	'types' => array(
		'1' => array('showitem' => 'subject, body, sent_time'),
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
		'sender' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.sender',
			'config' => array(
				'type' => 'input',
				'size' => 255,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'recipient' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.recipient',
			'config' => array(
				'type' => 'input',
				'size' => 255,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'subject' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.subject',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'body' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.body',
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
		'context' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.context',
			'config' => array(
				'type' => 'input',
				'size' => 255,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'attachment' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.attachment',
			'config' => array(
				'type' => 'input',
				'size' => 255,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'was_opened' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage.was_opened',
			'config' => array(
				'type' => 'input',
				'size' => 255,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
	),
	'grid' => array(
		'facets' => array(
			'uid',
			'subject',
		),
		'columns' => array(
			'__checkbox' => array(
				'width' => '5px',
				'sortable' => FALSE,
				'html' => '<input type="checkbox" class="checkbox-row-top"/>',
			),
			'uid' => array(
				'visible' => FALSE,
				'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf:uid',
				'width' => '5px',
			),
			'sender' => array(),
			'recipient' => array(),
			'subject' => array(),
			'body' => array(
				'width' => '500px',
			),
			'sent_time' => array(
				'format' => 'datetime',
				'width' => '150px',
			),
			'attachment' => array(
				'visible' => FALSE,
			),
			'was_opened' => array(
				'visible' => FALSE,
			),
			'context' => array(
				'width' => '100px',
			),
			'__buttons' => array(
				'sortable' => FALSE,
				'width' => '70px',
			),
		),
	),
);
