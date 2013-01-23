<?php
/*
 * Register necessary class names with autoloader
 *
 * $Id$
 */
$extensionPath = t3lib_extMgm::extPath('messenger');
return array(
	'tx_messenger_utility_configuration' => $extensionPath . 'Classes/Utility/Configuration.php',
	'tx_messenger_listmanager_registry' => $extensionPath . 'Classes/ListManager/Registry.php',
	'tx_messenger_domain_model_message' => $extensionPath . 'Classes/Domain/Model/Message.php',
	'tx_messenger_domain_repository_messagetemplaterepository' => $extensionPath . 'Classes/Domain/Repository/MessageTemplateRepository.php',
	'tx_messenger_validator_email' => $extensionPath . 'Classes/Validator/Email.php',
	'tx_messenger_utility_marker' => $extensionPath . 'Classes/Utility/Marker.php',
	'tx_messenger_utility_configuration' => $extensionPath . 'Classes/Utility/Configuration.php',
	'tx_messenger_utility_context' => $extensionPath . 'Classes/Utility/Context.php',
	'tx_messenger_utility_html2text' => $extensionPath . 'Classes/Utility/Html2Text.php',
	'tx_messenger_utility_object' => $extensionPath . 'Classes/Utility/Object.php',
);
?>
