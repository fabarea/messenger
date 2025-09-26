<?php

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

namespace Fab\Messenger\Components\Buttons;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Buttons\ButtonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class NewButton implements ButtonInterface
{
    protected string $link = '';

    protected ?ServerRequestInterface $request = null;

    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function isValid(): true
    {
        return true;
    }

    public function getType(): string
    {
        return static::class;
    }

    public function setRequest(?ServerRequestInterface $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }



    public function __toString()
    {
        return $this->render();
    }

    public function render(): string
    {
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:messenger/Resources/Private/Templates'],
            partialRootPaths: ['EXT:messenger/Resources/Private/Partials'],
            layoutRootPaths: ['EXT:messenger/Resources/Private/Layouts'],
            request: $this->request
        );

        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $view = $viewFactory->create($viewFactoryData);

        $view->assignMultiple([
            'link' => $this->link,
        ]);
        return $view->render($this->getTemplateName());
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
