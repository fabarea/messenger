<?php

namespace Fab\Messenger\Controller;

/*
 * This file is part of the Fab/Mailing project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Queue\QueueManager;
use Fab\Messenger\Service\DataExportService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MessageQueueController
 */
class MessageQueueController extends AbstractMessengerController
{
    /**
     * @var string
     */
    protected string $table = 'tx_messenger_domain_model_queue';

    protected array $allowedColumns = [
        'uid',
        'uuid',
        'pid',
        'recipient_cc',
        'recipient',
        'sender',
        'subject',
        'body',
        'attachment',
        'context',
        'mailing_name',
        'message_template',
        'message_layout',
        'scheduled_distribution_time',
        'ip',
        'error_count',
        'message_serialized',
        'redirect_email_from',
    ];

    protected array $defaultSelectedColumns = ['uid', 'recipient_cc', 'recipient', 'sender', 'subject', 'context'];

    protected array $demandFields = [
        'recipient_cc',
        'recipient',
        'sender',
        'subject',
        'body',
        'attachment',
        'context',
        'mailing_name',
        'message_template',
        'message_layout',
    ];

    protected string $domainModel = 'queue';

    protected string $controller = 'MessageQueue';

    protected string $action = 'index';

    protected string $moduleName = 'MessengerTxMessengerM4';

    protected string $dataType = 'message-queue';

    protected ?MessengerRepositoryInterface $repository;

    protected bool $showNewButton = true;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory           $iconFactory,
        DataExportService     $dataExportService
    )
    {
        parent::__construct($moduleTemplateFactory, $iconFactory, $dataExportService);
        $this->repository = GeneralUtility::makeInstance(QueueRepository::class);
    }

    /**
     * @param array $matches
     * @return string
     */
    public function confirmAction(array $matches = []): string
    {
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, $this->tableName);

        $numberOfRecipients = $this->getContentService()->findBy($matcher)->getNumberOfObjects();

        $label =
            $numberOfRecipients > 1
                ? $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.messages.sure?',
            )
                : $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.message.sure?',
            );

        return sprintf($label, $numberOfRecipients);
    }

    /**
     * @return ContentService
     */
    protected function getContentService(): ContentService
    {
        return GeneralUtility::makeInstance(ContentService::class, $this->tableName);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @param array $matches
     * @return string
     */
    public function dequeueAction(array $matches = []): string
    {
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, $this->tableName);

        // Fetch objects via the Content Service.
        $contentObjects = $this->getContentService()->findBy($matcher)->getObjects();

        $numberOfSentEmails = 0;
        $numberOfRecipients = is_countable($contentObjects) ? count($contentObjects) : 0;

        foreach ($contentObjects as $contentObject) {
            $isSent = $this->getQueueManager()->dequeueOne($contentObject['uid']);
            if ($isSent) {
                $numberOfSentEmails++;
            }
        }

        return sprintf(
            '%s %s / %s. %s',
            $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.success',
            ),
            $numberOfSentEmails,
            $numberOfRecipients,
            $numberOfSentEmails !== $numberOfRecipients
                ? $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.invalidEmails',
            )
                : '',
        );
    }

    /**
     * @return object|QueueManager
     */
    protected function getQueueManager(): QueueManager
    {
        return GeneralUtility::makeInstance(QueueManager::class);
    }
}
