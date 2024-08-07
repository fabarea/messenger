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

class MessageTemplateColumnSelectorButton implements ButtonInterface
{
    protected array $fields = [];

    protected array $selectedColumns = [];

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
                'Resources/Private/Templates/Components/Buttons/MessageTemplateColumnSelectorButton.html',
        );
        $view->assignMultiple([
            'selectedColumns' => $this->selectedColumns,
            'fields' => $this->fields,
        ]);
        return $view->render();
    }
}
