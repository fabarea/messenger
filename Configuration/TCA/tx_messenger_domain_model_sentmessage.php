<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

return array(
	'ctrl' => array(
		'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sent_message',
		'label' => 'subject',
		'default_sortby' => 'ORDER BY uid DESC',
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
		'1' => array('showitem' => 'sender, recipient, subject, body, sent_time, attachment, was_opened, context'),
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
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sender',
			'config' => array(
				'type' => 'input',
				'size' => 50,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'recipient' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:recipient',
			'config' => array(
				'type' => 'input',
				'size' => 50,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'subject' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:subject',
			'config' => array(
				'type' => 'input',
				'size' => 50,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'body' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:body',
			'config' => array(
				'type' => 'text',
				'rows' => 4,
				'cols' => 50,
				'readOnly' => TRUE,
			),
		),
		'sent_time' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:sent_time',
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
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:context',
			'config' => array(
				'type' => 'input',
				'size' => 50,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'attachment' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:attachment',
			'config' => array(
				'type' => 'input',
				'size' => 50,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'was_opened' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf:was_opened',
			'config' => array(
				'type' => 'input',
				'size' => 50,
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
				'renderer' => new \TYPO3\CMS\Vidi\Grid\CheckBoxComponent(),
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
				'sortable' => FALSE,
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
				'renderer' => new \TYPO3\CMS\Vidi\Grid\ButtonGroupComponent(),
			),
		),
	),
);
