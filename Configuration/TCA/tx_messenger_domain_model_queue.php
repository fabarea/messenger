<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

return array(
	'ctrl' => array(
		'title' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_queue',
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
			'default' => 'extensions-messenger-queue',
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
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_queue.sender',
			'config' => array(
				'type' => 'input',
				'size' => 255,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'recipient' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_queue.recipient',
			'config' => array(
				'type' => 'input',
				'size' => 255,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'subject' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_queue.subject',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'body' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_queue.body',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'context' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_queue.context',
			'config' => array(
				'type' => 'input',
				'size' => 255,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'attachment' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_queue.attachment',
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
			'attachment' => array(
				'visible' => FALSE,
			),
			'context' => array(
				'width' => '100px',
			),
		),
	),
);
