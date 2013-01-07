<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'user',	 // Make module a submodule of 'user'
		'm1',	// Submodule key
		'',						// Position
		array(
			'Backend' => 'index',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_m1.xlf',
		)
	);

}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Send a message to a group of people');

t3lib_extMgm::addLLrefForTCAdescr('tx_messenger_domain_model_sentmessage', 'EXT:messenger/Resources/Private/Language/locallang_csh_tx_messenger_domain_model_sentmessage.xlf');
t3lib_extMgm::allowTableOnStandardPages('tx_messenger_domain_model_sentmessage');
$TCA['tx_messenger_domain_model_sentmessage'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:messenger/Resources/Private/Language/locallang_db.xlf:tx_messenger_domain_model_sentmessage',
		'label' => 'user',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'verssOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'searchFields' => 'content,',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/SentMessage.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_messenger_domain_model_sentmessage.gif'
	),
);

?>