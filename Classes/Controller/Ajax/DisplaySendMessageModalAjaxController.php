<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Repository\PageRepository;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Fab\Messenger\Service\SenderProvider;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

class DisplaySendMessageModalAjaxController extends AbstractMessengerAjaxController
{
    protected ?RecipientRepository $repository;
    protected PageRepository $pageRepository;

    public function __construct()
    {
        $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }


    public function displayAction(): ResponseInterface
    {

        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:messenger/Resources/Private/Templates'],
            partialRootPaths: ['EXT:messenger/Resources/Private/Partials'],
            layoutRootPaths: ['EXT:messenger/Resources/Private/Layouts'],
            request: $this->getRequest()
        );

        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $view = $viewFactory->create($viewFactoryData);
        $pageContent = $this->pageRepository->findByUid($this->getPageId());
        $view->assignMultiple([
            'senders' => GeneralUtility::makeInstance(SenderProvider::class)->getFormattedPossibleSenders(),
            'title' => $pageContent['title'] ?? '',
        ]);

        return $this->getResponse($view->render($this->getTemplateName()));

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
