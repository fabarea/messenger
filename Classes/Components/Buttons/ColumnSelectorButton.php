<?php

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

namespace Fab\Messenger\Components\Buttons;

use TYPO3\CMS\Backend\Template\Components\Buttons\ButtonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('messenger') .
                'Resources/Private/Standalone/Components/Buttons/ColumnSelectorButton.html',
        );
        $view->assignMultiple([
            'selectedColumns' => $this->selectedColumns,
            'fields' => $this->fields,
            'tableName' => $this->tableName,
            'module' => $this->module,
            'controller' => $this->controller,
            'action' => $this->action,
            'model' => $this->model,
        ]);
        return $view->render();
    }
}
