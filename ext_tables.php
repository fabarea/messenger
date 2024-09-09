<?php

use Fab\Messenger\Controller\BackendMessageController;
use Fab\Messenger\Controller\MessageLayoutController;
use Fab\Messenger\Controller\MessageQueueController;
use Fab\Messenger\Controller\MessageSentController;
use Fab\Messenger\Controller\MessageTemplateController;
use Fab\Messenger\Controller\SentMessageModuleController;
use Fab\Messenger\Utility\ConfigurationUtility;
use Fab\Messenger\View\MenuItem\DequeueMenuItem;
use Fab\Messenger\View\MenuItem\SendAgainMenuItem;
use Fab\Messenger\View\MenuItem\SendMenuItem;
use Fab\Vidi\Module\ModuleLoader;
use Fab\Vidi\View\Button\NewButton;
use Fab\Vidi\View\MenuItem\DividerMenuItem;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    // Add static TypoScript template
    ExtensionManagementUtility::addStaticFile(
        'messenger',
        'Configuration/TypoScript',
        'Send a message to a group of people',
    );

    // Allow domain model to be on standard pages.
    ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_sentmessage');
    ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_messagetemplate');
    ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_messagelayout');
    ExtensionManagementUtility::allowTableOnStandardPages('tx_messenger_domain_model_queue');

    $icons = [
        'sentmessage' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.png',
        'messagetemplate' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagetemplate.png',
        'messagelayout' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagelayout.png',
        'queue' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_queue.png',
    ];

    /** @var IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    foreach ($icons as $key => $icon) {
        $iconRegistry->registerIcon('extensions-messenger-' . $key, BitmapIconProvider::class, [
            'source' => $icon,
        ]);
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
        ExtensionManagementUtility::addModule(
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
    if (class_exists(ModuleLoader::class)) {
        // Register newsletter BE module
        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('messenger');

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
                ->addMenuMassActionComponents([SendAgainMenuItem::class, DividerMenuItem::class])
                ->register();
        }
        if (!isset($configuration['load_message_queue_module']) || (bool) $configuration['load_message_queue_module']) {
            \Fab\Messenger\Module\ModuleLoader::register('tx_messenger_domain_model_queue')
                ->addMenuMassActionComponents([DequeueMenuItem::class, DividerMenuItem::class])
                ->register();
        }

        ExtensionUtility::registerModule(
            'Fab.Messenger',
            'messenger',
            'tx_messenger_m1',
            'top',
            [
                SentMessageModuleController::class => 'index',
            ],
            [
                'access' => 'admin',
                'icon' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.svg',
                'labels' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf',
            ],
        );

        ExtensionUtility::registerModule(
            'Fab.Messenger',
            'messenger',
            'tx_messenger_m2',
            'bottom',
            [
                MessageTemplateController::class => 'index',
            ],
            [
                'access' => 'admin',
                'icon' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagetemplate.svg',
                'labels' =>
                    'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagetemplate.xlf',
            ],
        );

        ExtensionUtility::registerModule(
            'Fab.Messenger',
            'messenger',
            'tx_messenger_m3',
            'bottom',
            [
                MessageLayoutController::class => 'index',
            ],
            [
                'access' => 'admin',
                'icon' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagelayout.svg',
                'labels' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf',
            ],
        );

        ExtensionUtility::registerModule(
            'Fab.Messenger',
            'messenger',
            'tx_messenger_m4',
            'bottom',
            [
                MessageQueueController::class => 'index',
            ],
            [
                'access' => 'admin',
                'icon' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_queue.svg',
                'labels' => 'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_queue.xlf',
            ],
        );

        ExtensionUtility::registerModule(
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

        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('

                    # Hide the module in the BE.
                    options.hideModules.user := addToList(MessengerM1)

                ');

        $loadNewsletterModule = (bool) ConfigurationUtility::getInstance()->get('load_newsletter_module');

        if ($loadNewsletterModule) {
            $recipientDataType = ConfigurationUtility::getInstance()->get('recipient_data_type');

            // Register a new BE Module to send newsletter
            /** @var ModuleLoader $moduleLoader */
            $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);
            $moduleLoader
                ->ignorePid(true)
                ->setMainModule('web')
                ->setDataType($recipientDataType)
                ->setIcon('EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.svg')
                ->setModuleLanguageFile('LLL:EXT:messenger/Resources/Private/Language/module_newsletter.xlf')
                ->removeComponentFromDocHeader(NewButton::class)
                ->addMenuMassActionComponents([SendMenuItem::class, DividerMenuItem::class])
                ->ignorePid(true)
                ->register();

            // Special case for fe_users to add a special menu
            if ($recipientDataType === 'fe_users') {
                /** @var ModuleLoader $moduleLoader */
                $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);

                /** @var ModuleLoader $moduleLoader */
                $moduleLoader
                    ->setDataType('fe_users')
                    ->setModuleLanguageFile('LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf')
                    ->setIcon('EXT:vidi/Resources/Public/Images/fe_users.svg')
                    ->addMenuMassActionComponents([SendMenuItem::class, DividerMenuItem::class])
                    ->register();
            }
        }
    }
});
