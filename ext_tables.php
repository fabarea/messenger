<?php

use Fab\Messenger\Controller\BackendMessageController;
use Fab\Messenger\Controller\MessageQueueController;
use Fab\Messenger\Controller\MessageSentController;

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    // Add static TypoScript template
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'messenger',
        'Configuration/TypoScript',
        'Send a message to a group of people',
    );

    // Allow domain model to be on standard pages.
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
        'tx_messenger_domain_model_sentmessage',
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
        'tx_messenger_domain_model_messagetemplate',
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
        'tx_messenger_domain_model_messagelayout',
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_queue');

    $icons = [
        'sentmessage' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.png',
        'messagetemplate' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagetemplate.png',
        'messagelayout' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagelayout.png',
        'queue' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_queue.png',
    ];

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($icons as $key => $icon) {
        $iconRegistry->registerIcon(
            'extensions-messenger-' . $key,
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            [
                'source' => $icon,
            ],
        );
    }
    unset($iconRegistry);

    // Add Messenger main module before 'user'
    if (!isset($GLOBALS['TBE_MODULES']['messenger'])) {
        $beModules = [];
        foreach ($GLOBALS['TBE_MODULES'] as $key => $val) {
            if ($key === 'user') {
                $beModules['messenger'] = '';
            }
            $beModules[$key] = $val;
        }
        $GLOBALS['TBE_MODULES'] = $beModules;

        // Module Dms
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
            'messenger',
            '',
            '', // does not work for a main module before:tools
            '',
            [
                #'routeTarget' => \Hemmer\MrDmsExport\Controller\BackendModuleController::class . '::mainAction',
                'access' => 'group,user',
                'icon' => 'EXT:messenger/Resources/Public/Icons/module-messenger.svg',
                'labels' => 'LLL:EXT:messenger/Resources/Private/Language/module_messenger.xlf',
            ],
        );
    }

    // Load some vidi BE modules
    if (class_exists(\Fab\Vidi\Module\ModuleLoader::class)) {
        // Register newsletter BE module
        $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class,
        )->get('messenger');

        if (
            !isset($configuration['load_message_template_module']) ||
            (bool) $configuration['load_message_template_module']
        ) {
            \Fab\Messenger\Module\ModuleLoader::register('tx_messenger_domain_model_messagetemplate');
        }
        if (
            !isset($configuration['load_message_layout_module']) ||
            (bool) $configuration['load_message_layout_module']
        ) {
            \Fab\Messenger\Module\ModuleLoader::register('tx_messenger_domain_model_messagelayout');
        }
        if (!isset($configuration['load_message_sent_module']) || (bool) $configuration['load_message_sent_module']) {
            \Fab\Messenger\Module\ModuleLoader::register('tx_messenger_domain_model_sentmessage')
                ->addMenuMassActionComponents([
                    \Fab\Messenger\View\MenuItem\SendAgainMenuItem::class,
                    \Fab\Vidi\View\MenuItem\DividerMenuItem::class,
                ])
                ->register();
        }
        if (!isset($configuration['load_message_queue_module']) || (bool) $configuration['load_message_queue_module']) {
            \Fab\Messenger\Module\ModuleLoader::register('tx_messenger_domain_model_queue')
                ->addMenuMassActionComponents([
                    \Fab\Messenger\View\MenuItem\DequeueMenuItem::class,
                    \Fab\Vidi\View\MenuItem\DividerMenuItem::class,
                ])
                ->register();
        }

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Fab.Messenger',
            'messenger',
            'tx_messenger_m1',
            'top',
            [
                \Fab\Messenger\Controller\AdminModuleController::class => 'index',
            ],
            [
                'access' => 'admin',
                'icon' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.svg',
                'labels' => 'LLL:EXT:messenger/Resources/Private/Language/locallang_mod.xlf',
            ],
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Fab.messenger',
            'user', // Make media module a submodule of 'user'
            'm1',
            'bottom', // Position
            [
                BackendMessageController::class => 'compose, enqueue, sendAsTest, feedbackSent, feedbackQueued',
                MessageQueueController::class => 'confirm, dequeue',
                MessageSentController::class => 'confirm, sendAgain',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:messenger/ext_icon.svg',
                'labels' => 'LLL:EXT:messenger/Resources/Private/Language/module_messenger.xlf',
            ],
        );

        // Default User TSConfig to be added in any case.
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('

                    # Hide the module in the BE.
                    options.hideModules.user := addToList(MessengerM1)

                ');

        $loadNewsletterModule = (bool) \Fab\Messenger\Utility\ConfigurationUtility::getInstance()->get(
            'load_newsletter_module',
        );

        if ($loadNewsletterModule) {
            $recipientDataType = \Fab\Messenger\Utility\ConfigurationUtility::getInstance()->get('recipient_data_type');

            // Register a new BE Module to send newsletter
            /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
            $moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Fab\Vidi\Module\ModuleLoader::class);
            $moduleLoader
                ->ignorePid(true)
                ->setMainModule('web')
                ->setDataType($recipientDataType)
                ->setIcon('EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.svg')
                ->setModuleLanguageFile('LLL:EXT:messenger/Resources/Private/Language/module_newsletter.xlf')
                ->removeComponentFromDocHeader(\Fab\Vidi\View\Button\NewButton::class)
                ->addMenuMassActionComponents([
                    \Fab\Messenger\View\MenuItem\SendMenuItem::class,
                    \Fab\Vidi\View\MenuItem\DividerMenuItem::class,
                ])
                ->ignorePid(true)
                ->register();

            // Special case for fe_users to add a special menu
            if ($recipientDataType === 'fe_users') {
                /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
                $moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    \Fab\Vidi\Module\ModuleLoader::class,
                );

                /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
                $moduleLoader
                    ->setDataType('fe_users')
                    ->setModuleLanguageFile('LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf')
                    ->setIcon('EXT:vidi/Resources/Public/Images/fe_users.svg')
                    ->addMenuMassActionComponents([
                        \Fab\Messenger\View\MenuItem\SendMenuItem::class,
                        \Fab\Vidi\View\MenuItem\DividerMenuItem::class,
                    ])
                    ->register();
            }
        }
    }
});
