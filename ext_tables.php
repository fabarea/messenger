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

 
});
