<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Components\Buttons\ColumnSelectorButton;
use Fab\Messenger\Components\Buttons\NewButton;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Fab\Messenger\Resolver\FieldPathResolver;
use Fab\Messenger\Service\BackendUserPreferenceService;
use Fab\Messenger\Service\DataExportService;
use Fab\Messenger\Utility\ConfigurationUtility;
use Psr\Http\Message\ResponseInterface;
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

class RecipientModuleController extends ActionController
{
    protected ?RecipientRepository $repository;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;
    protected ModuleTemplate $moduleTemplate;

    protected DataExportService $dataExportService;

    protected int $itemsPerPage = 20;

    protected array $allowedColumns = [];
    protected bool $showNewButton = false;
    protected array $excludedFields = ['l10n_parent', 'l10n_diffsource', 'sys_language_uid'];

    protected string $tableName = '';

    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
        $this->tableName = ConfigurationUtility::getInstance()->get('recipient_data_type');
    }

    /**
     * @throws NoSuchArgumentException
     * @throws RouteNotFoundException
     */
    public function indexAction(): ResponseInterface
    {
        $orderings = $this->getOrderings();
        $records = $this->repository->findByDemand($this->getDemand(), $orderings);
        $items = $this->request->hasArgument('items') ? $this->request->getArgument('items') : $this->itemsPerPage;
        $currentPage = $this->request->hasArgument('page') ? $this->request->getArgument('page') : 1;
        $paginator = new ArrayPaginator($records, $currentPage, $items);

        $selectedColumns = $this->computeSelectedColumns();
        $pagination = new SimplePagination($paginator);
        $this->view->assignMultiple([
            'recipients' => $records,
            'selectedColumns' => $selectedColumns,
            'fields' => $this->getFields(),
            'paginator' => $paginator,
            'pagination' => $pagination,
            'domainModel' => $this->tableName,
            'currentPage' => $this->request->hasArgument('page') ? $this->request->getArgument('page') : 1,
            'count' => count($records),
            'sortBy' => key($orderings),
            'searchTerm' => $this->request->hasArgument('searchTerm') ? $this->request->getArgument('searchTerm') : '',
            'itemsPerPages' => $this->request->hasArgument('items')
                ? $this->request->getArgument('items')
                : $this->itemsPerPage,
            'direction' => $orderings[key($orderings)],
            'selectedRecords' => $this->request->hasArgument('selectedRecords')
                ? $this->request->getArgument('selectedRecords')
                : [],
        ]);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->moduleTemplate->setContent($this->view->render());
        $this->computeDocHeader($this->getFields(), $selectedColumns);

        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * @throws NoSuchArgumentException
     */
    protected function getOrderings(): array
    {
        $sortBy = $this->request->hasArgument('sortBy') ? $this->request->getArgument('sortBy') : 'crdate';

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

    /**
     * @throws NoSuchArgumentException
     */
    protected function getDemand(): array
    {
        $searchTerm = $this->request->hasArgument('searchTerm') ? $this->request->getArgument('searchTerm') : '';
        $demand = [];
        $demandFields = GeneralUtility::trimExplode(
            ',',
            ConfigurationUtility::getInstance()->get('recipient_default_fields'),
            true,
        );
        if (strlen($searchTerm) > 0) {
            foreach ($demandFields as $field) {
                $demand['likes'][$field] = $searchTerm;
            }
        }
        return $demand;
    }

    /**
     * @throws NoSuchArgumentException
     */
    protected function computeSelectedColumns(): array
    {
        $defaultSelectedColumns = array_slice($this->getFields(), 0, 6);

        $moduleVersion = explode('/', $this->getRequestUri());
        if (count(array_unique($moduleVersion)) !== 1) {
            BackendUserPreferenceService::getInstance()->set('selectedColumns', $defaultSelectedColumns);
        }
        $selectedColumns =
            BackendUserPreferenceService::getInstance()->get('selectedColumns') ?? $defaultSelectedColumns;
        if ($this->request->hasArgument('selectedColumns')) {
            $selectedColumns = $this->request->getArgument('selectedColumns');
            BackendUserPreferenceService::getInstance()->set('selectedColumns', $selectedColumns);
        }
        return $selectedColumns;
    }

    protected function getFields(): array
    {
        return GeneralUtility::trimExplode(',', ConfigurationUtility::getInstance()->get('recipient_default_fields'));
    }

    private function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @throws RouteNotFoundException
     */
    private function computeDocHeader(array $fields, array $selectedColumns): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        /** @var ColumnSelectorButton $columnSelectorButton */
        $columnSelectorButton = $buttonBar->makeButton(ColumnSelectorButton::class);
        $columnSelectorButton->setFields($fields)->setSelectedColumns($selectedColumns);
        $columnSelectorButton->setModule('tx_messenger_messenger_messengertxmessengerm5');
        $columnSelectorButton->setTableName($this->tableName);
        $columnSelectorButton->setAction('index');
        $columnSelectorButton->setController('RecipientModule');
        $columnSelectorButton->setModel($this->getModel());

        if ($this->showNewButton) {
            $this->addNewButton();
        }

        $buttonBar->addButton($columnSelectorButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
    }

    protected function getModel(): string
    {
        return match ($this->tableName) {
            'tx_messenger_domain_model_messagetemplate' => 'messagetemplate',
            'tx_messenger_domain_model_messagelayout' => 'messagelayout',
            'tx_messenger_domain_model_sentmessage' => 'sentmessage',
            'tx_messenger_domain_model_queue' => 'queue',
            'fe_users' => 'fe_users',
            default => '',
        };
    }

    /**
     * @throws RouteNotFoundException
     */
    public function addNewButton(): RecipientModuleController
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $newButton = $buttonBar->makeButton(NewButton::class);
        $pagePid = $this->getConfigurationUtility()->get('rootPageUid');
        $newButton->setLink(
            $this->renderUriNewRecord([
                'table' => $this->repository->getTableName(),
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
        $arguments['returnUrl'] = $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri();
        $params = [
            'edit' => [$arguments['table'] => [$arguments['uid'] ?? ($arguments['pid'] ?? 0) => 'new']],
            'returnUrl' => $arguments['returnUrl'],
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string) $uriBuilder->buildUriFromRoute('record_edit', $params);
    }
}
