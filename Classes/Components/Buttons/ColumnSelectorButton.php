<?php

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

namespace Fab\Messenger\Components\Buttons;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Buttons\ButtonInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\View\ViewFactoryData;
use Fab\Messenger\Controller\Ajax\ColumnSelectorController;

class ColumnSelectorButton implements ButtonInterface
{
    protected array $fields = [];

    protected array $selectedColumns = [];

    protected string $tableName = '';

    protected string $path = '';

    protected string $module = '';

    protected string $controller = '';

    protected string $action = '';

    protected string $model = '';

    protected ?ServerRequestInterface $request = null;


    public function getRequest(): ServerRequestInterface
    {
        return $this->request;

    }

    public function setRequest(?ServerRequestInterface $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function setSelectedColumns(array $columns): self
    {
        $this->selectedColumns = $columns;
        return $this;
    }

    public function getType(): string
    {
        return static::class;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function render(): string
    {
        // Récupérer les colonnes sauvegardées par l'utilisateur
        $savedColumns = ColumnSelectorController::getSavedColumnSelection($this->module, $this->tableName);

        // Si des colonnes sont sauvegardées, les utiliser, sinon garder les valeurs par défaut
        if (!empty($savedColumns)) {
            $this->selectedColumns = $savedColumns;
        }

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFile(
            'EXT:messenger/Resources/Public/JavaScript/ColumnSelector.js',
            'text/javascript',
            false,
            false,
            '',
            true
        );
        $pageRenderer->addCssFile(
            'EXT:messenger/Resources/Public/Css/ColumnSelector.css'
        );
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:messenger/Resources/Private/Templates'],
            partialRootPaths: ['EXT:messenger/Resources/Private/Partials'],
            layoutRootPaths: ['EXT:messenger/Resources/Private/Layouts'],
            request: $this->request
        );

        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $view = $viewFactory->create($viewFactoryData);

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        // Utiliser une route AJAX existante de TYPO3 avec des paramètres personnalisés
        try {
            $ajaxUrl = $uriBuilder->buildUriFromRoute('record_process');
        } catch (RouteNotFoundException $e) {
            // Fallback vers une URL directe
            $ajaxUrl = '/typo3/index.php?route=/record/process';
        }

        $view->assignMultiple([
            'selectedColumns' => $this->selectedColumns,
            'fields' => $this->fields,
            'tableName' => $this->tableName,
            'module' => $this->module,
            'controller' => $this->controller,
            'action' => $this->action,
            'model' => $this->model,
            'ajaxUrl' => $ajaxUrl,
        ]);
        return $view->render($this->getTemplateName());
    }

    /**
     * Get AJAX route name based on module
     */
    private function getAjaxRouteNameForModule(string $module): string
    {
        $routeMap = [
            'tx_messenger_messenger_messengertxmessengerm1' => 'messenger_column_selector_m1',
            'tx_messenger_messenger_messengertxmessengerm2' => 'messenger_column_selector_m2',
            'tx_messenger_messenger_messengertxmessengerm3' => 'messenger_column_selector_m3',
            'tx_messenger_messenger_messengertxmessengerm4' => 'messenger_column_selector_m4',
            'tx_messenger_messenger_messengertxmessengerm5' => 'messenger_column_selector_m5',
        ];

        return $routeMap[$module] ?? 'messenger_column_selector_m1';
    }

    /**
     * Get AJAX path based on module for fallback
     */
    private function getAjaxPathForModule(string $module): string
    {
        $pathMap = [
            'tx_messenger_messenger_messengertxmessengerm1' => '/messenger/column-selector/m1',
            'tx_messenger_messenger_messengertxmessengerm2' => '/messenger/column-selector/m2',
            'tx_messenger_messenger_messengertxmessengerm3' => '/messenger/column-selector/m3',
            'tx_messenger_messenger_messengertxmessengerm4' => '/messenger/column-selector/m4',
            'tx_messenger_messenger_messengertxmessengerm5' => '/messenger/column-selector/m5',
        ];

        return $pathMap[$module] ?? '/messenger/column-selector/m1';
    }

    protected function getTemplateName(): string
    {
        $className = get_class($this);
        $classNameParts = explode('\\', $className);
        $controllerName = end($classNameParts);

        $controllerName = str_replace('Controller', '', $controllerName);

        return $controllerName . '/Index';
    }
}
