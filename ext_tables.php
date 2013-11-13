<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE' && \TYPO3\CMS\Messenger\Utility\Configuration::getInstance()->get('enableBeModule')) {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		$_EXTKEY,
		'user',	 // Make module a submodule of 'user'
		'm1',	// Submodule key
		'',						// Position
		array(
			'Backend' => 'index, sendMessage, sendMessageTest',
			'ListManager' => 'list, save',
			'MessageTemplate' => 'list, save',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_m1.xlf',
		)
	);

}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Send a message to a group of people');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_messenger_domain_model_sentmessage', 'EXT:messenger/Resources/Private/Language/locallang_csh_tx_messenger_domain_model_sentmessage.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_sentmessage');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_messagetemplate');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_messagelayout');

$icons = array(
	'messagetemplate' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_messenger_domain_model_messagetemplate.gif',
	'messagelayout' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_messenger_domain_model_messagelayout.png',
	'sentmessage' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_messenger_domain_model_sentmessage.png',
);
\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons($icons, $_EXTKEY);

?>