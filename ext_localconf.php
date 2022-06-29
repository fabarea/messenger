<?php

use Fab\Messenger\Controller\MessageDisplayController;

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

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'messenger',
            'MessageDisplay',
            [
                MessageDisplayController::class => 'show',
            ],
            // non-cacheable actions
            [
                MessageDisplayController::class => 'show',
            ]
        );

        // eID for resolving Frontend URL in the context of the Backend.
        $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['messenger'] = \Fab\Messenger\PagePath\Resolver::class . '::resolveUrl';

        // Add caching framework garbage collection task
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Fab\Messenger\Task\MessengerDequeueTask::class] = [
            'extension' => 'messenger',
            'title' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:scheduler.dequeue.name',
            'description' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:scheduler.dequeue.description',
            'additionalFields' => \Fab\Messenger\Task\MessengerDequeueFieldProvider::class
        ];

    }
);
