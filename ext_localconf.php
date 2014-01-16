<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

# Register a new list manager for demo purposes.
\TYPO3\CMS\Messenger\ListManager\Registry::getInstance()->add(

	# Corresponds to a class name.
	'TYPO3\CMS\Messenger\ListManager\DemoListManager',

	# A string or label describing the recipients (for the BE module needs).
	'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:demo_list_of_recipients'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
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

# Install PSR-0-compatible class autoloader for Markdown Library in Resources/PHP/Michelf
spl_autoload_register(function ($class) {
	if (strpos($class, 'Michelf\Markdown') !== FALSE) {
		require sprintf('%sResources/Private/PHP/Markdown/%s',
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger'),
			preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')) . '.php'
		);
	}
});
?>