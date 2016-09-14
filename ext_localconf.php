<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'messenger',
    'Pi1',
    array(
        'MessageRenderer' => 'render',
    ),
    // non-cacheable actions
    array(
        'MessageRenderer' => 'render',
    )
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \Fab\Messenger\Command\MessageQueueCommandController::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \Fab\Messenger\Command\LogCommandController::class;

// Override classes for the Object Manager
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Core\Mail\MailMessage'] = [
    'className' => 'Fab\Messenger\Override\Core\Mail\MailMessage'
];

// eID for resolving Frontend URL in the context of the Backend.
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['messenger'] = 'EXT:messenger/Classes/PagePath/Resolver.php';

# Install PSR-0-compatible class autoloader for Markdown Library in Resources/PHP/Michelf
spl_autoload_register(function ($class) {
    if (strpos($class, 'Michelf\Markdown') !== FALSE) {
        require sprintf('%sResources/Private/PHP/Markdown/%s',
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger'),
            preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')) . '.php'
        );
    }
});
