<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MessageTemplateController extends AbstractMessengerController
{
    protected array $allowedColumns = [
        'uid',
        'type',
        'hidden',
        'qualifier',
        'subject',
        'source_file',
        'source_page',
        'template_engine',
        'body',
        'message_layout',
    ];

    protected array $defaultSelectedColumns = ['uid', 'subject', 'body'];

    protected array $demandFields = ['type', 'subject', 'message_layout', 'qualifier'];
    protected string $domainModel = 'messagetemplate';
    protected string $controller = 'MessageTemplate';
    protected string $action = 'index';
    protected string $moduleName = 'MessengerTxMessengerM2';
    protected string $table = 'tx_messenger_domain_model_messagetemplate';
    protected ?MessengerRepositoryInterface $repository;
    protected string $dataType = 'message-template';
    protected bool $showNewButton = true;

    public function __construct()
    {
        parent::__construct();
        $this->repository = GeneralUtility::makeInstance(MessageTemplateRepository::class);
    }
}
