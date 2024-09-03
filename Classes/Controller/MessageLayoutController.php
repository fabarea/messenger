<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MessageLayoutController extends AbstractMessengerController
{
    protected array $allowedColumns = ['uid', 'content', 'qualifier', 'hidden'];

    protected array $defaultSelectedColumns = ['uid', 'content', 'qualifier', 'hidden'];

    protected array $demandFields = ['content', 'qualifier'];

    protected string $domainModel = 'messagelayout';

    protected string $controller = 'MessageLayout';

    protected string $action = 'index';

    protected string $moduleName = 'tx_messenger_messenger_messengertxmessengerm3';

    protected string $table = 'tx_messenger_domain_model_messagelayout';

    protected string $repositoryName = 'MessageLayoutRepository';

    protected ?MessengerRepositoryInterface $repository;

    protected bool $showNewButton = true;

    public function __construct()
    {
        parent::__construct();
        $this->repository = GeneralUtility::makeInstance(MessageLayoutRepository::class);
    }
}
