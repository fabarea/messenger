<?php

use Fab\Messenger\Controller\MessageLayoutController;
use Fab\Messenger\Controller\MessageQueueController;
use Fab\Messenger\Controller\MessageTemplateController;
use Fab\Messenger\Controller\SentMessageModuleController;
use Fab\Messenger\Module\MessengerModule;
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
                'access' => 'group,user',
                'icon' => 'EXT:messenger/Resources/Public/Icons/module-messenger.svg',
                'labels' => 'LLL:EXT:messenger/Resources/Private/Language/module_messenger.xlf',
            ],
        );
    }

    // Load some messenger BE modules
    if (class_exists(MessengerModule::class)) {
        // Register newsletter BE module
        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('messenger');

        if (
            !isset($configuration['load_message_template_module']) ||
            (bool) $configuration['load_message_template_module']
        ) {
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
        }
        if (
            !isset($configuration['load_message_layout_module']) ||
            (bool) $configuration['load_message_layout_module']
        ) {
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
                    'labels' =>
                        'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_messagelayout.xlf',
                ],
            );
        }
        if (!isset($configuration['load_message_sent_module']) || (bool) $configuration['load_message_sent_module']) {
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
                    'labels' =>
                        'LLL:EXT:messenger/Resources/Private/Language/tx_messenger_domain_model_sentmessage.xlf',
                ],
            );
        }
        if (!isset($configuration['load_message_queue_module']) || (bool) $configuration['load_message_queue_module']) {
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
        }
        if (!isset($configuration['load_newsletter_module']) || (bool) $configuration['load_newsletter_module']) {
            $recipientDataType = (new Fab\Messenger\Domain\Repository\RecipientRepository())->getTableName();
            switch ($recipientDataType) {
                case 'tx_messenger_domain_model_sentmessage':
                    $controller = SentMessageModuleController::class;
                    break;
                case 'tx_messenger_domain_model_messagetemplate':
                    $controller = MessageTemplateController::class;
                    break;
                case 'tx_messenger_domain_model_messagelayout':
                    $controller = MessageLayoutController::class;
                    break;
                case 'tx_messenger_domain_model_queue':
                    $controller = MessageQueueController::class;
                    break;
                default:
                    $controller = MessageQueueController::class;
                    break;
            }
            ExtensionUtility::registerModule(
                'Fab.Messenger',
                'web',
                'tx_messenger_m5',
                'bottom',
                [
                    $controller => 'index',
                ],

                [
                    'access' => 'admin',
                    'icon' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.svg',
                    'labels' => 'LLL:EXT:messenger/Resources/Private/Language/module_newsletter.xlf',
                ],
            );
        }
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('

                    # Hide the module in the BE.
                    options.hideModules.user := addToList(MessengerM1)

                ');
    }
});
