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

class NewButton implements ButtonInterface
{
    protected string $link = '';

    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function isValid()
    {
        return true;
    }

    public function getType()
    {
        return static::class;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render(): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('messenger') .
                'Resources/Private/Standalone/Components/Buttons/NewButton.html',
        );
        $view->assignMultiple([
            'link' => $this->link,
        ]);
        return $view->render();
    }
}
