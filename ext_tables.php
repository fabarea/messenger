<?php

use Fab\Messenger\Service\BackendModuleService;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    ExtensionManagementUtility::addStaticFile(
        'messenger',
        'Configuration/TypoScript',
        'Send a message to a group of people',
    );

    $icons = [
        'module' => [
            'source' => 'EXT:messenger/Resources/Public/Icons/module-messenger.svg',
            'provider' => SvgIconProvider::class,
        ],
        'sentmessage' => [
            'source' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_sentmessage.png',
            'provider' => BitmapIconProvider::class,
        ],
        'messagetemplate' => [
            'source' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagetemplate.png',
            'provider' => BitmapIconProvider::class,
        ],
        'messagelayout' => [
            'source' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_messagelayout.png',
            'provider' => BitmapIconProvider::class,
        ],
        'queue' => [
            'source' => 'EXT:messenger/Resources/Public/Icons/tx_messenger_domain_model_queue.png',
            'provider' => BitmapIconProvider::class,
        ],
    ];

    /** @var IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    foreach ($icons as $key => $icon) {
        $iconRegistry->registerIcon('extensions-messenger-' . $key, $icon['provider'], [
            'source' => $icon['source'],
        ]);
    }
    unset($iconRegistry);

    try {
        /** @var BackendModuleService $backendModuleService */
        $backendModuleService = GeneralUtility::makeInstance(BackendModuleService::class);
        $hiddenModules = $backendModuleService->getHiddenModules();

        if (!empty($hiddenModules)) {
            $hiddenModulesList = implode(',', $hiddenModules);
            ExtensionManagementUtility::addUserTSConfig('
                # Hide modules based on extension configuration
                options.hideModules := addToList(' . $hiddenModulesList . ')
            ');
        }
    } catch (\Exception $e) {
    }
});
