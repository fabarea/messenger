<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Components\Buttons\ColumnSelectorButton;
use Fab\Messenger\Components\Buttons\NewButton;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Service\BackendUserPreferenceService;
use Fab\Messenger\Service\DataExportService;
use Fab\Messenger\Utility\ConfigurationUtility;
use Fab\Messenger\Utility\TcaFieldsUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

abstract class AbstractMessengerController extends ActionController
{
    protected ?MessengerRepositoryInterface $repository;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;
    protected ModuleTemplate $moduleTemplate;

    protected DataExportService $dataExportService;
    protected int $itemsPerPage = 20;
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

    protected string $dataType = '';

    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->dataExportService = GeneralUtility::makeInstance(DataExportService::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
    }

    /**
     * @throws NoSuchArgumentException
     */
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
        $fields = array_merge(['uid'], $fields);
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
            'moduleName' => $this->moduleName,
            'dataType' => $this->dataType,
            'selectedRecords' => $this->request->hasArgument('selectedRecords')
                ? $this->request->getArgument('selectedRecords')
                : [],
        ]);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->computeDocHeader($fields, $selectedColumns);
        return $this->moduleTemplate->renderResponse();
    }

    /**
     * @throws NoSuchArgumentException
     */
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
                $demand['likes'][$field] = $searchTerm;
            }
        }
        return $demand;
    }

    protected function computeSelectedColumns(): array
    {
        $moduleVersion = explode('/', $this->getRequestUrl());
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

    private function getRequestUri(): string
    {
        return $this->request->getAttribute('normalizedParams')->getRequestUrl();
    }

    private function computeDocHeader(array $fields, array $selectedColumns): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        /** @var ColumnSelectorButton $columnSelectorButton */
        $columnSelectorButton = $buttonBar->makeButton(ColumnSelectorButton::class);
        $columnSelectorButton->setFields($fields)->setSelectedColumns($selectedColumns);
        $columnSelectorButton->setModule($this->getModuleName($this->moduleName));
        $columnSelectorButton->setTableName($this->table);
        $columnSelectorButton->setAction('index');
        $columnSelectorButton->setController($this->controller);
        $columnSelectorButton->setModel($this->domainModel);

        if ($this->showNewButton) {
            $this->addNewButton();
        }

        $buttonBar->addButton($columnSelectorButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
    }

    protected function getModuleName(string $signature): string
    {
        switch ($signature) {
            case 'MessengerTxMessengerM1':
                return 'tx_messenger_messenger_messengertxmessengerm1';
            case 'MessengerTxMessengerM2':
                return 'tx_messenger_messenger_messengertxmessengerm2';
            case 'MessengerTxMessengerM3':
                return 'tx_messenger_messenger_messengertxmessengerm3';
            case 'MessengerTxMessengerM4':
                return 'tx_messenger_messenger_messengertxmessengerm4';
            case 'MessengerTxMessengerM5':
                return 'tx_messenger_messenger_messengertxmessengerm5';
            default:
                return '';
        }
    }

    /**
     * @throws RouteNotFoundException
     */
    public function addNewButton(): AbstractMessengerController
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $newButton = $buttonBar->makeButton(NewButton::class);
        $pagePid = $this->getConfigurationUtility()->get('rootPageUid');
        $newButton->setLink(
            $this->renderUriNewRecord([
                'table' => $this->table,
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

    /**
     * @throws RouteNotFoundException
     */
    protected function renderUriNewRecord(array $arguments): string
    {
        $arguments['returnUrl'] = $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUrl();
        $params = [
            'edit' => [$arguments['table'] => [$arguments['uid'] ?? ($arguments['pid'] ?? 0) => 'new']],
            'returnUrl' => $arguments['returnUrl'],
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string) $uriBuilder->buildUriFromRoute('record_edit', $params);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
