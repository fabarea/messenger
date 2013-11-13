<?php
/*
 * Register necessary class names with autoloader
 *
 * $Id$
 */
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger');
return array(
	'TYPO3\CMS\Messenger\Utility\Configuration' => $extensionPath . 'Classes/Utility/Configuration.php',
	'TYPO3\CMS\Messenger\ListManager\Registry' => $extensionPath . 'Classes/ListManager/Registry.php',
	'tx_messenger_domain_model_message' => $extensionPath . 'Classes/Domain/Model/Message.php',
	'\TYPO3\CMS\Messenger\Domain\Repository\MessageTemplateRepository' => $extensionPath . 'Classes/Domain/Repository/MessageTemplateRepository.php',
	'TYPO3\CMS\Messenger\Validator\Email' => $extensionPath . 'Classes/Validator/Email.php',
	'TYPO3\CMS\Messenger\Utility\Marker' => $extensionPath . 'Classes/Utility/Marker.php',
	'TYPO3\CMS\Messenger\Utility\Configuration' => $extensionPath . 'Classes/Utility/Configuration.php',
	'TYPO3\CMS\Messenger\Utility\Context' => $extensionPath . 'Classes/Utility/Context.php',
	'TYPO3\CMS\Messenger\Utility\Html2Text' => $extensionPath . 'Classes/Utility/Html2Text.php',
	'TYPO3\CMS\Messenger\Utility\Object' => $extensionPath . 'Classes/Utility/Object.php',
);
?>
