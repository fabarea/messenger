<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Service\DataExportService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MessageLayoutController extends AbstractMessengerController
{
    protected array $allowedColumns = ['uid', 'content', 'qualifier', 'hidden'];

    protected array $defaultSelectedColumns = ['uid', 'content', 'qualifier', 'hidden'];

    protected array $demandFields = ['content', 'qualifier'];

    protected string $domainModel = 'messagelayout';

    protected string $controller = 'MessageLayout';

    protected string $action = 'index';

    protected string $moduleName = 'MessengerTxMessengerM3';

    protected string $table = 'tx_messenger_domain_model_messagelayout';

    protected string $dataType = 'message-layout';

    protected ?MessengerRepositoryInterface $repository;

    protected bool $showNewButton = true;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory           $iconFactory,
        DataExportService     $dataExportService
    )
    {
        parent::__construct($moduleTemplateFactory, $iconFactory, $dataExportService);
        $this->repository = GeneralUtility::makeInstance(MessageLayoutRepository::class);
    }
}
