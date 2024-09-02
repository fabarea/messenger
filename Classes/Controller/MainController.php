<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Components\Buttons\ColumnSelectorButton;
use Fab\Messenger\Components\Buttons\NewButton;
use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Service\BackendUserPreferenceService;
use Fab\Messenger\Service\DataExportService;
use Fab\Messenger\Utility\ConfigurationUtility;
use Fab\Messenger\Utility\TcaFieldsUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class MainController extends ActionController
{
    protected MessageLayoutRepository|SentMessageRepository|MessageTemplateRepository|null $repository = null;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;
    protected ModuleTemplate $moduleTemplate;

    protected DataExportService $dataExportService;
    protected int $itemsPerPage = 20;
    protected int $maximumLinks = 10;
    protected array $allowedColumns = [];

    protected string $table = '';

    protected bool $showNewButton = false;
    protected array $defaultSelectedColumns = [];

    protected array $demandFields = [];

    protected string $controller = '';

    protected string $action = '';

    protected string $domainModel = '';
    protected array $excludedFields = ['l10n_parent', 'l10n_diffsource', 'sys_language_uid'];
    protected string $moduleName = '';

    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->dataExportService = GeneralUtility::makeInstance(DataExportService::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
    }

    public function getRepository(): SentMessageRepository|MessageLayoutRepository|MessageTemplateRepository
    {
        return $this->repository;
    }

    public function setRepository(
        SentMessageRepository|MessageLayoutRepository|MessageTemplateRepository $repository,
    ): self {
        $this->repository = $repository;
        return $this;
    }

    public function getDomainModel(): string
    {
        return $this->domainModel;
    }

    public function setDomainModel(string $domainModel): self
    {
        $this->domainModel = $domainModel;
        return $this;
    }

    public function getAllowedColumns(): array
    {
        return $this->allowedColumns;
    }

    public function setAllowedColumns(array $allowedColumns): self
    {
        $this->allowedColumns = $allowedColumns;
        return $this;
    }

    public function getDefaultSelectedColumns(): array
    {
        return $this->defaultSelectedColumns;
    }

    public function setDefaultSelectedColumns(array $defaultSelectedColumns): self
    {
        $this->defaultSelectedColumns = $defaultSelectedColumns;
        return $this;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function setModuleName(string $moduleName): self
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    public function getMaximumLinks(): int
    {
        return $this->maximumLinks;
    }

    public function setMaximumLinks(int $maximumLinks): self
    {
        $this->maximumLinks = $maximumLinks;
        return $this;
    }

    public function getDemandFields(): array
    {
        return $this->demandFields;
    }

    public function setDemandFields(array $demandFields): self
    {
        $this->demandFields = $demandFields;
        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function indexAction(): ResponseInterface
    {
        $orderings = $this->getOrderings();
        $messages = $this->repository->findByDemand($this->getDemand(), $orderings);
        $items = $this->request->hasArgument('items') ? $this->request->getArgument('items') : $this->itemsPerPage;
        $currentPage = $this->request->hasArgument('page') ? $this->request->getArgument('page') : 1;
        $paginator = new ArrayPaginator($messages, $currentPage, $items);
        $fields = TcaFieldsUtility::getFields($this->table);
        $fields = array_filter($fields, function ($field) {
            return !in_array($field, $this->excludedFields);
        });
        $selectedColumns = $this->computeSelectedColumns();
        $pagination = new SimplePagination($paginator);
        $this->view->assignMultiple([
            'messages' => $messages,
            'selectedColumns' => $selectedColumns,
            'fields' => $fields,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'currentPage' => $this->request->hasArgument('page') ? $this->request->getArgument('page') : 1,
            'count' => count($messages),
            'sortBy' => key($orderings),
            'searchTerm' => $this->request->hasArgument('searchTerm') ? $this->request->getArgument('searchTerm') : '',
            'itemsPerPages' => $this->request->hasArgument('items')
                ? $this->request->getArgument('items')
                : $this->itemsPerPage,
            'direction' => $orderings[key($orderings)],
            'controller ' => $this->controller,
            'action' => $this->action,
            'domainModel' => $this->domainModel,
            'selectedRecords' => $this->request->hasArgument('selectedRecords')
                ? $this->request->getArgument('selectedRecords')
                : [],
        ]);

        if (
            $this->request->hasArgument('selectedRecords') &&
            $this->request->getArgument('selectedRecords') !== '' &&
            $this->request->hasArgument('btnAction') &&
            $this->request->getArgument('btnAction') !== null
        ) {
            $uids = $this->request->hasArgument('selectedRecords')
                ? $this->request->getArgument('selectedRecords')
                : [];
            $format = $this->request->getArgument('btnAction');
            $columns = $this->computeSelectedColumns();
            array_unshift($columns, 'uid');
            $columns = array_filter($columns);
            $columns = array_unique($columns);
            $this->exportAction($uids, $format, $columns);
        }

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setContent($this->view->render());
        $this->computeDocHeader($fields, $selectedColumns);

        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    protected function getOrderings(): array
    {
        $sortBy = $this->request->hasArgument('sortBy') ? $this->request->getArgument('sortBy') : 'crdate';
        if (!in_array($sortBy, $this->allowedColumns)) {
            $sortBy = 'crdate';
        }
        $defaultDirection = QueryInterface::ORDER_DESCENDING;
        $direction = $this->request->hasArgument('direction')
            ? $this->request->getArgument('direction')
            : $defaultDirection;
        if ($this->request->hasArgument('direction') && strtoupper($direction) === 'DESC') {
            $defaultDirection = QueryInterface::ORDER_ASCENDING;
        }
        return [
            $sortBy => $defaultDirection,
        ];
    }

    protected function getDemand(): array
    {
        $searchTerm = $this->request->hasArgument('searchTerm') ? $this->request->getArgument('searchTerm') : '';
        $demand = [];

        if (strlen($searchTerm) > 0) {
            foreach ($this->demandFields as $field) {
                $demand[$field] = $searchTerm;
            }
        }
        return $demand;
    }

    protected function computeSelectedColumns(): array
    {
        $currentUrl = $_SERVER['REQUEST_URI'];
        $moduleVersion = explode('/', $currentUrl);
        if (count(array_unique($moduleVersion)) !== 1) {
            BackendUserPreferenceService::getInstance()->set('selectedColumns', $this->defaultSelectedColumns);
        }

        $selectedColumns =
            BackendUserPreferenceService::getInstance()->get('selectedColumns') ?? $this->defaultSelectedColumns;

        if ($this->request->hasArgument('selectedColumns')) {
            $selectedColumns = $this->request->getArgument('selectedColumns');
            BackendUserPreferenceService::getInstance()->set('selectedColumns', $selectedColumns);
        }
        return $selectedColumns;
    }

    public function exportAction(array $uids, string $format, array $columns): void
    {
        $this->dataExportService->setRepository($this->repository);

        switch ($format) {
            case 'csv':
                $this->dataExportService->exportCsv($uids, 'export.csv', ',', '"', '\\', $columns);
                break;
            case 'xls':
                $this->dataExportService->exportXls($uids, 'export.xls', $columns);
                break;
            case 'xml':
                $this->dataExportService->exportXml($uids, 'export.xml', $columns);
                break;
        }
    }

    private function computeDocHeader(array $fields, array $selectedColumns): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        /** @var ColumnSelectorButton $columnSelectorButton */
        $columnSelectorButton = $buttonBar->makeButton(ColumnSelectorButton::class);
        $columnSelectorButton->setFields($fields)->setSelectedColumns($selectedColumns);
        $columnSelectorButton->setModule($this->moduleName);
        $columnSelectorButton->setTableName($this->table);
        $columnSelectorButton->setAction('index');
        $columnSelectorButton->setController($this->controller);
        $columnSelectorButton->setModel($this->domainModel);

        if ($this->showNewButton) {
            $this->addNewButton();
        }

        $buttonBar->addButton($columnSelectorButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function addNewButton(): MainController
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $newButton = $buttonBar->makeButton(NewButton::class);
        $pagePid = $this->getConfigurationUtility()->get('rootPageUid');

        $newButton->setLink(
            $this->renderUriNewRecord([
                'table' => 'tx_messenger_domain_model_messagetemplate',
                'pid' => $pagePid,
                'uid' => 0,
            ]),
        );
        $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 2);

        return $this;
    }

    protected function getConfigurationUtility(): ConfigurationUtility
    {
        return GeneralUtility::makeInstance(ConfigurationUtility::class);
    }

    protected function renderUriNewRecord(array $arguments): string
    {
        $arguments['returnUrl'] = $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri();

        $params = [
            'edit' => [$arguments['table'] => [$arguments['uid'] ?? ($arguments['pid'] ?? 0) => 'new']],
            'returnUrl' => $arguments['returnUrl'],
        ];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string) $uriBuilder->buildUriFromRoute('record_edit', $params);
    }

    protected function setShowNewButton(bool $showNewButton): void
    {
        $this->showNewButton = $showNewButton;
    }
}
