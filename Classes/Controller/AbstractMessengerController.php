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
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Core\Page\PageRenderer;

abstract class AbstractMessengerController extends ActionController
{

    protected ?MessengerRepositoryInterface $repository;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;
    protected DataExportService $dataExportService;
    protected PageRenderer $pageRenderer;
    protected BackendViewFactory $backendViewFactory;

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

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory $iconFactory,
        DataExportService $dataExportService,
        PageRenderer $pageRenderer,
        BackendViewFactory $backendViewFactory
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->iconFactory = $iconFactory;
        $this->dataExportService = $dataExportService;
        $this->pageRenderer = $pageRenderer;
        $this->backendViewFactory = $backendViewFactory;
    }

    /**
     * @throws NoSuchArgumentException
     */
    public function indexAction(): ResponseInterface
    {
        // Créer la vue backend avec les bons chemins de templates
        $view = $this->backendViewFactory->create($this->request);
        
        $orderings = $this->getOrderings();
        $items = $this->request->hasArgument('items') ? $this->request->getArgument('items') : $this->itemsPerPage;
        $currentPage = $this->request->hasArgument('page') ? $this->request->getArgument('page') : 1;
        $offset = ($currentPage - 1) * $items;
        
        // Récupérer le nombre total d'éléments pour la pagination
        $totalCount = $this->getTotalCount();
        
        // Récupérer seulement les messages de la page courante
        $messages = $this->repository->findByDemand($this->getDemand(), $orderings, $offset, $items);
        $paginator = new ArrayPaginator($messages, $currentPage, $items, $totalCount);

        $fields = TcaFieldsUtility::getFields($this->table);
        $fields = array_filter($fields, function ($field) {
            return !in_array($field, $this->excludedFields);
        });
        $fields = array_merge(['uid'], $fields);

        $selectedColumns = $this->computeSelectedColumns();
        $pagination = new SlidingWindowPagination($paginator, 5);

        // Calculer le nombre total de pages
        $totalPages = (int) ceil($totalCount / $items);
        $prevPage = max(1, $currentPage - 1);
        $nextPage = min($totalPages, $currentPage + 1);
        
        $view->assignMultiple([
            'messages' => $messages,
            'selectedColumns' => $selectedColumns,
            'fields' => $fields,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'currentPage' => $this->request->hasArgument('page') ? $this->request->getArgument('page') : 1,
            'count' => $totalCount,
            'totalPages' => $totalPages,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
            'sortBy' => key($orderings),
            'searchTerm' => $this->request->hasArgument('searchTerm') ? $this->request->getArgument('searchTerm') : '',
            'itemsPerPages' => $this->request->hasArgument('items')
                ? $this->request->getArgument('items')
                : $this->itemsPerPage,
            'direction' => $orderings[key($orderings)],
            'controller' => $this->controller,
            'action' => $this->action,
            'domainModel' => $this->domainModel,
            'moduleName' => $this->moduleName,
            'dataType' => $this->dataType,
            'selectedRecords' => $this->request->hasArgument('selectedRecords')
                ? $this->request->getArgument('selectedRecords')
                : [],
        ]);

        // Configurer le docheader pour la vue Extbase
        $this->configureDocHeaderForExtbase($view, $fields, $selectedColumns);

        return $view->renderResponse('Index');
    }

    protected function configureDocHeaderForExtbase($view, array $fields, array $selectedColumns): void
    {
        try {
            // Créer un ModuleTemplate pour le docheader
            $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
            $docHeaderComponent = $moduleTemplate->getDocHeaderComponent();
            $docHeaderComponent->enable();
            
            $buttonBar = $docHeaderComponent->getButtonBar();

            // Ajouter le bouton de sélection de colonnes
            if (class_exists(ColumnSelectorButton::class)) {
                /** @var ColumnSelectorButton $columnSelectorButton */
                $columnSelectorButton = $buttonBar->makeButton(ColumnSelectorButton::class);
                $columnSelectorButton
                    ->setFields($fields)
                    ->setSelectedColumns($selectedColumns)
                    ->setModule($this->getModuleName($this->moduleName))
                    ->setTableName($this->table)
                    ->setAction('index')
                    ->setController($this->controller)
                    ->setModel($this->domainModel);

                $buttonBar->addButton($columnSelectorButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
            }

            if ($this->showNewButton) {
                $this->addNewButtonToDocHeader($moduleTemplate);
            }

            // Assigner le ModuleTemplate à la vue pour le rendu
            $this->view->assign('moduleTemplate', $moduleTemplate);
            
        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog(
                'Error in configureDocHeaderForExtbase: ' . $e->getMessage(),
                'messenger',
                3
            );
        }
    }

    protected function addNewButtonToDocHeader(ModuleTemplate $moduleTemplate): void
    {
        try {
            $docHeaderComponent = $moduleTemplate->getDocHeaderComponent();
            $buttonBar = $docHeaderComponent->getButtonBar();

            if (class_exists(NewButton::class)) {
                /** @var NewButton $newButton */
                $newButton = $buttonBar->makeButton(NewButton::class);
                $newButton
                    ->setModule($this->getModuleName($this->moduleName))
                    ->setTableName($this->table)
                    ->setAction('new')
                    ->setController($this->controller)
                    ->setModel($this->domainModel);

                $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
            }
        } catch (\Exception $e) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog(
                'Error in addNewButtonToDocHeader: ' . $e->getMessage(),
                'messenger',
                3
            );
        }
    }

    protected function initializeModuleTemplate(ServerRequestInterface $request): ModuleTemplate
    {
        $view = $this->moduleTemplateFactory->create($request);
        
        // Définir le titre du module pour TYPO3 v12
        $view->setTitle('Messenger Module');
        
        // S'assurer que le docheader est activé
        $docHeaderComponent = $view->getDocHeaderComponent();
        $docHeaderComponent->enable();
        
        // Assigner le ModuleTemplate à la vue pour qu'il soit disponible dans les templates
        $view->assign('moduleTemplate', $view);
        
        return $view;
    }

    private function modifyDocHeaderComponent(ModuleTemplate $view, array $fields, array $selectedColumns): void
    {
        try {
            $docHeaderComponent = $view->getDocHeaderComponent();
            
            // S'assurer que le docheader est activé dans TYPO3 v12
            $docHeaderComponent->enable();
            
            $buttonBar = $docHeaderComponent->getButtonBar();

            // Ajouter le bouton de sélection de colonnes
            if (class_exists(ColumnSelectorButton::class)) {
                /** @var ColumnSelectorButton $columnSelectorButton */
                $columnSelectorButton = $buttonBar->makeButton(ColumnSelectorButton::class);
                $columnSelectorButton
                    ->setFields($fields)
                    ->setSelectedColumns($selectedColumns)
                    ->setModule($this->getModuleName($this->moduleName))
                    ->setTableName($this->table)
                    ->setAction('index')
                    ->setController($this->controller)
                    ->setModel($this->domainModel);

                $buttonBar->addButton($columnSelectorButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
            }

            if ($this->showNewButton) {
                $this->addNewButton($view);
            }
        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog(
                'Error in modifyDocHeaderComponent: ' . $e->getMessage(),
                'messenger',
                3
            );
        }
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

    protected function getTotalCount(): int
    {
        // Récupérer le nombre total d'éléments correspondant aux critères de recherche
        $demand = $this->getDemand();
        return $this->repository->countByDemand($demand);
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

    private function getRequestUrl(): string
    {
        return $this->request->getAttribute('normalizedParams')->getRequestUrl();
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
    protected function addNewButton(ModuleTemplate $view): void
    {
        try {
            $docHeaderComponent = $view->getDocHeaderComponent();
            $buttonBar = $docHeaderComponent->getButtonBar();

            if (class_exists(NewButton::class)) {
                /** @var NewButton $newButton */
                $newButton = $buttonBar->makeButton(NewButton::class);
                $pagePid = $this->getConfigurationUtility()->get('rootPageUid');

                $newButton->setLink(
                    $this->renderUriNewRecord([
                        'table' => $this->table,
                        'pid' => $pagePid,
                        'uid' => 0,
                    ])
                );

                $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
            }
        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog(
                'Error in addNewButton: ' . $e->getMessage(),
                'messenger',
                3
            );
        }
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
        $arguments['returnUrl'] = $this->request->getAttribute('normalizedParams')->getRequestUrl();
        $params = [
            'edit' => [$arguments['table'] => [$arguments['uid'] ?? ($arguments['pid'] ?? 0) => 'new']],
            'returnUrl' => $arguments['returnUrl'],
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string) $uriBuilder->buildUriFromRoute('record_edit', $params);
    }
}
