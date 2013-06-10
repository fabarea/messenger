<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

# Register a new list manager for demo purposes.
Tx_Messenger_ListManager_Registry::getInstance()->add(

	# Corresponds to a class name.
	'Tx_Messenger_ListManager_DemoListManager',

	# A string or label describing the recipients (for the BE module needs).
	'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:demo_list_of_recipients'
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Pi1',
	array(
		'MessageRenderer' => 'render',
	),
	// non-cacheable actions
	array(
		'MessageRenderer' => 'render',
	)
);

?>