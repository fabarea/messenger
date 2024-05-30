<?php

use Fab\Messenger\Controller\AdminModuleController;
use Fab\Messenger\Controller\MessageDisplayController;
use Fab\Messenger\PagePath\Resolver;
use Fab\Messenger\Task\MessengerDequeueFieldProvider;
use Fab\Messenger\Task\MessengerDequeueTask;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    $configuration = GeneralUtility::makeInstance(
        ExtensionConfiguration::class,
    )->get('messenger');

    if (!isset($configuration['autoload_typoscript']) || (bool) $configuration['autoload_typoscript']) {
        ExtensionManagementUtility::addTypoScript(
            'messenger',
            'constants',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:messenger/Configuration/TypoScript/constants.txt">',
        );

        ExtensionManagementUtility::addTypoScript(
            'messenger',
            'setup',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:messenger/Configuration/TypoScript/setup.txt">',
        );
    }

    ExtensionUtility::configurePlugin(
        'messenger',
        'Pi1',
        [
            'MessageRenderer' => 'render',
        ],
        // non-cacheable actions
        [
            'MessageRenderer' => 'render',
        ],
    );

    ExtensionUtility::configurePlugin(
        'messenger',
        'MessageDisplay',
        [
            MessageDisplayController::class => 'show',
            AdminModuleController::class => 'index',
        ],
        // non-cacheable actions
        [
            MessageDisplayController::class => 'show',
            AdminModuleController::class => 'index',
        ],
    );

    // eID for resolving Frontend URL in the context of the Backend.
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['messenger'] =
        Resolver::class . '::resolveUrl';

    // Add caching framework garbage collection task
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][MessengerDequeueTask::class] = [
        'extension' => 'messenger',
        'title' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:scheduler.dequeue.name',
        'description' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:scheduler.dequeue.description',
        'additionalFields' => MessengerDequeueFieldProvider::class,
    ];
});
