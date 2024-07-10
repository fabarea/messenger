<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Components\Buttons\ColumnSelectorButton;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Service\BackendUserPreferenceService;
use Fab\Messenger\Service\DataExportService;
use Fab\Messenger\Utility\TcaFieldsUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class SendMessageModuleController extends ActionController
{
    protected SentMessageRepository $sentMessageRepository;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;
    protected DataExportService $dataExportService;
    protected int $itemsPerPage = 20;
    protected int $maximumLinks = 10;

    protected array $defaultSelectedColumns = ['sender', 'subject', 'context', 'recipient', 'sent_time'];
    protected ModuleTemplate $moduleTemplate;
    private array $allowedSortBy = [
        'uid',
        'crdate',
        'tstamp',
        'sender',
        'subject',
        'mailing_name',
        'recipient',
        'sent_time',
        'context',
        'body',
        'recipient_cc',
        'recipient_bcc',
        'redirect_email_from',
        'attachment',
        'message_template',
        'message_layout',
        'ip',
        'was_opened',
        'scheduled_distribution_time',
        'uuid',
    ];

    public function __construct()
    {
        $this->sentMessageRepository = GeneralUtility::makeInstance(SentMessageRepository::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->dataExportService = GeneralUtility::makeInstance(DataExportService::class);
    }

    public function indexAction(): ResponseInterface
    {
        $orderings = $this->getOrderings();
        $messages = $this->sentMessageRepository->findByDemand($this->getDemand(), $orderings);
        $items = $this->request->hasArgument('items') ? $this->request->getArgument('items') : $this->itemsPerPage;
        $currentPage = $this->request->hasArgument('page') ? $this->request->getArgument('page') : 1;
        $searchTerm = $this->request->hasArgument('searchTerm') ? $this->request->getArgument('searchTerm') : '';
        $paginator = new ArrayPaginator($messages, $currentPage, $items);
        $fields = TcaFieldsUtility::getFields();
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
                'sender' => $searchTerm,
                'subject' => $searchTerm,
                'mailing_name' => $searchTerm,
                'recipient' => $searchTerm,
                'sent_time' => $searchTerm,
            ];
        }
        return $demand;
    }

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
        $buttonBar->addButton($columnSelectorButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
    }
}
