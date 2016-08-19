<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Add static TypoScript template
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('messenger', 'Configuration/TypoScript', 'Send a message to a group of people');

// Allow domain model to be on standard pages.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_sentmessage');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_messagetemplate');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_messagelayout');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_queue');

// Sprite icon
$icons['sentmessage'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('messenger') . 'Resources/Public/Icons/tx_messenger_domain_model_sentmessage.png';
$icons['messagetemplate'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('messenger') . 'Resources/Public/Icons/tx_messenger_domain_model_messagetemplate.png';
$icons['messagelayout'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('messenger') . 'Resources/Public/Icons/tx_messenger_domain_model_messagelayout.png';
$icons['queue'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('messenger') . 'Resources/Public/Icons/tx_messenger_domain_model_queue.png';

\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons($icons, 'messenger');

// Add Messenger main module before 'user'
// There are not API for doing this... ;(
// Some hope with this patch merged into 6.2 http://forge.typo3.org/issues/49643?
if (TYPO3_MODE === 'BE') {
    if (!isset($GLOBALS['TBE_MODULES']['messenger'])) {
        $beModules = [];
        foreach ($GLOBALS['TBE_MODULES'] as $key => $val) {
            if ($key === 'user') {
                $beModules['messenger'] = '';
            }
            $beModules[$key] = $val;
        }
        $GLOBALS['TBE_MODULES'] = $beModules;
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('messenger', '', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger') . 'mod/messenger/');
    }
}

if (TYPO3_MODE === 'BE') {

    // Load some vidi BE modules
    if (class_exists('Fab\Vidi\Module\ModuleLoader')) {

        \Fab\Messenger\Module\ModuleLoader::register('messagetemplate');
        \Fab\Messenger\Module\ModuleLoader::register('messagelayout');
        \Fab\Messenger\Module\ModuleLoader::register('sentmessage');
        \Fab\Messenger\Module\ModuleLoader::register('queue');

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Fab.messenger',
            'user', // Make media module a submodule of 'user'
            'm1',
            'bottom', // Position
            array(
                'BackendMessage' => 'compose, send, sendAsTest, feedback',
            ),
            array(
                'access' => 'user,group',
                'icon' => 'EXT:messenger/ext_icon.png',
                'labels' => 'LLL:EXT:messenger/Resources/Private/Language/module_messenger.xlf',
            )
        );

        // Default User TSConfig to be added in any case.
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('

            # Hide the module in the BE.
            options.hideModules.user := addToList(MessengerM1)

        ');

        // Extend FE User module
        /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
        $moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Fab\Vidi\Module\ModuleLoader::class,
            'fe_users'
        );

        $moduleLoader->addMenuMassActionComponents([
            \Fab\Messenger\View\MenuItem\SendMenuItem::class,
            \Fab\Vidi\View\MenuItem\DividerMenuItem::class,
        ]);
        $moduleLoader->register();
    }
}
