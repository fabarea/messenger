<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {

        $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get('messenger');

        if (!isset($configuration['autoload_typoscript']) || (bool)$configuration['autoload_typoscript']) {

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                'messenger',
                'constants',
                '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:messenger/Configuration/TypoScript/constants.txt">'
            );

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                'messenger',
                'setup',
                '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:messenger/Configuration/TypoScript/setup.txt">'
            );
        }

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'messenger',
            'Pi1',
            [
                'MessageRenderer' => 'render',
            ],
            // non-cacheable actions
            [
                'MessageRenderer' => 'render',
            ]
        );

        // Override classes for the Object Manager
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Mail\MailMessage::class] = [
            'className' => \Fab\Messenger\Override\Core\Mail\MailMessage::class
        ];

        // eID for resolving Frontend URL in the context of the Backend.
        $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['messenger'] = \Fab\Messenger\PagePath\Resolver::class . '::resolveUrl';

        # Install PSR-0-compatible class autoloader for Markdown Library in Resources/PHP/Michelf
        spl_autoload_register(function ($class) {
            if (strpos($class, 'Michelf\Markdown') !== FALSE) {
                require sprintf('%sResources/Private/PHP/Markdown/%s',
                    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger'),
                    preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')) . '.php'
                );
            }
        });

    }
);
