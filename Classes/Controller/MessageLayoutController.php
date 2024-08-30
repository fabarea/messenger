<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Components\Buttons\ColumnSelectorButton;
use Fab\Messenger\Components\Buttons\NewButton;
use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
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
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class MessageLayoutController extends ActionController
{
    protected MessageLayoutRepository $messageLayout;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;
    protected DataExportService $dataExportService;
    protected int $itemsPerPage = 20;
    protected int $maximumLinks = 10;

    protected array $defaultSelectedColumns = ['uid', 'content', 'qualifier', 'hidden'];
    protected ModuleTemplate $moduleTemplate;
    protected array $includedFIELDS = ['l10n_parent', 'l10n_diffsource', 'sys_language_uid'];
    private array $allowedSortBy = ['uid', 'content', 'qualifier', 'hidden'];

    public function __construct()
    {
        $this->messageLayout = GeneralUtility::makeInstance(MessageLayoutRepository::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->dataExportService = GeneralUtility::makeInstance(DataExportService::class);
    }

    public function indexAction(): ResponseInterface
    {
        $orderings = $this->getOrderings();
        $messages = $this->messageLayout->findByDemand($this->getDemand(), $orderings);
        $items = $this->request->hasArgument('items') ? $this->request->getArgument('items') : $this->itemsPerPage;
        $currentPage = $this->request->hasArgument('page') ? $this->request->getArgument('page') : 1;
        $searchTerm = $this->request->hasArgument('searchTerm') ? $this->request->getArgument('searchTerm') : '';
        $paginator = new ArrayPaginator($messages, $currentPage, $items);
        $fields = TcaFieldsUtility::getFields('tx_messenger_domain_model_messagelayout');
        $fields = array_diff($fields, $this->includedFIELDS);
        array_unshift($fields, 'uid');

        $selectedColumns = $this->computeSelectedColumns();

        $pagination = new SimplePagination($paginator);
        $this->view->assignMultiple([
            'messages' => $messages,
            'selectedColumns' => $selectedColumns,
            'fields' => $fields,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'currentPage' => $currentPage,
            'count' => count($messages),
            'sortBy' => key($orderings),
            'searchTerm' => $searchTerm,
            'itemsPerPages' => $items,
            'direction' => $orderings[key($orderings)],
            'controller ' => 'MessageLayoutController',
            'action' => 'index',
            'domainModel' => 'messagelayout',
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
        if (!in_array($sortBy, $this->allowedSortBy)) {
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
            $demand = [
                'content' => $searchTerm,
                'qualifier' => $searchTerm,
            ];
        }
        return $demand;
    }

    /**
     * @throws NoSuchArgumentException
     */
    protected function computeSelectedColumns(): array
    {
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
        $columnSelectorButton->setModule('tx_messenger_messenger_messengertxmessengerm3');
        $columnSelectorButton->setTableName('tx_messenger_domain_model_messagelayout');
        $columnSelectorButton->setAction('index');
        $columnSelectorButton->setController('MessageLayout');
        $columnSelectorButton->setModel('messagelayout');

        $newButton = $buttonBar->makeButton(NewButton::class);
        $pagePid = $this->getConfigurationUtility()->get('rootPageUid');

        $newButton->setLink(
            $this->renderUriNewRecord([
                'table' => 'tx_messenger_domain_model_messagelayout',
                'pid' => $pagePid,
                'uid' => 0,
            ]),
        );
        $buttonBar->addButton($columnSelectorButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
        $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
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
}
