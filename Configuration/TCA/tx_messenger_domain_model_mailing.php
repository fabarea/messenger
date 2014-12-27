<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

return array(
	'ctrl' => array(
		'title' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_mailing.xlf:mailing',
		'label' => 'user',
		'default_sortby' => 'ORDER BY uid ASC',
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
		'1' => array('showitem' => 'title, comment, sent_time'),
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
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_mailing.xlf:title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'comment' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_mailing.xlf:comment',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'readOnly' => TRUE,
				'eval' => 'trim',
			),
		),
		'sent_time' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_mailing.xlf:sent_time',
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
	'grid' => array(
		'facets' => array(
			'uid',
			'title',
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
			'title' => array(),
			'__buttons' => array(
				'renderer' => new \TYPO3\CMS\Vidi\Grid\ButtonGroupComponent(),
			),
		),
	),
);
