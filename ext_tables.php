<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Add static TypoScript template
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Send a message to a group of people');

// Allow domain model to be on standard pages.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_sentmessage');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_messagetemplate');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_messagelayout');

// Add sprites
$icons = array(
	'messagetemplate' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_messenger_domain_model_messagetemplate.png',
	'messagelayout' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_messenger_domain_model_messagelayout.png',
	'sentmessage' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_messenger_domain_model_sentmessage.png',
);
\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons($icons, $_EXTKEY);

// Add Messenger main module before 'user'
// There are not API for doing this... ;(
// Some hope with this patch merged into 6.2 http://forge.typo3.org/issues/49643?
if (TYPO3_MODE == 'BE') {
	if (!isset($GLOBALS['TBE_MODULES']['messenger'])) {
		$beModules = array();
		foreach ($GLOBALS['TBE_MODULES'] as $key => $val) {
			if ($key == 'user') {
				$beModules['messenger'] = '';
			}
			$beModules[$key] = $val;
		}
		$GLOBALS['TBE_MODULES'] = $beModules;
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('messenger', '', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger') . 'mod/messenger/');
	}
}

if (TYPO3_MODE === 'BE' && \TYPO3\CMS\Messenger\Utility\Configuration::getInstance()->get('enableBeModule')) {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		$_EXTKEY,
		'messenger', // Make module a submodule of 'user'
		'm1', // Submodule key
		'', // Position
		array(
			'Backend' => 'index, sendMessage, sendMessageTest',
			'ListManager' => 'list, save',
			'MessageTemplate' => 'list, save',
		),
		array(
			'access' => 'user,group',
			'icon' => 'EXT:' . $_EXTKEY . '/ext_icon.png',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/module_composer.xlf',
		)
	);
}

// Load some vidi BE modules
if (class_exists('TYPO3\CMS\Vidi\ModuleLoader')) {

	$dataTypes = array('tx_messenger_domain_model_messagetemplate', 'tx_messenger_domain_model_messagelayout', 'tx_messenger_domain_model_sentmessage');

	foreach ($dataTypes as $dataType) {
		/** @var \TYPO3\CMS\Vidi\ModuleLoader $moduleLoader */
		$moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ModuleLoader', $dataType);
		$moduleLoader->setIcon('EXT:messenger/Resources/Public/Icons/' . $dataType . '.png')
			->setModuleLanguageFile('LLL:EXT:messenger/Resources/Private/Language/' . $dataType . '.xlf')
			->addJavaScriptFiles(array(sprintf('EXT:messenger/Resources/Public/JavaScript/Backend/%s.js', $dataType)))
			->addStyleSheetFiles(array(sprintf('EXT:messenger/Resources/Public/StyleSheet/Backend/%s.css', $dataType)))
			->setDefaultPid(1)
			->setMainModule('messenger')
			->register();
	}
}

?>