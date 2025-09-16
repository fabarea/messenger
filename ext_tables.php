<?php

use Fab\Messenger\Controller\MessageLayoutController;
use Fab\Messenger\Controller\MessageQueueController;
use Fab\Messenger\Controller\MessageTemplateController;
use Fab\Messenger\Controller\RecipientModuleController;
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

    $icons = [
        'sentmessage' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.svg',
        'messagetemplate' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagetemplate.svg',
        'messagelayout' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagelayout.svg',
        'queue' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_queue.svg',
        'module' => 'EXT:messenger/Resources/Public/Icons/module-messenger.svg'
    ];

    /** @var IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    foreach ($icons as $key => $icon) {
        $iconRegistry->registerIcon('extensions-messenger-' . $key, BitmapIconProvider::class, [
            'source' => $icon,
        ]);
    }
    unset($iconRegistry);
});
