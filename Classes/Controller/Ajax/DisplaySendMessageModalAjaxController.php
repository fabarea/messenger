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
        $pageTitle = $pageContent['title'] ?? '';
        $view->assignMultiple([
            'senders' => GeneralUtility::makeInstance(SenderProvider::class)->getFormattedPossibleSenders(),
            'title' => $pageTitle,
            'emailSubject' => $pageTitle,
        ]);

        $html = $view->render($this->getTemplateName());
        // Inject page title into subject field (Fluid variables may not resolve in this ViewFactory context)
        $html = str_replace('__MESSENGER_PAGE_TITLE__', htmlspecialchars((string)$pageTitle, ENT_QUOTES, 'UTF-8'), $html);

        return $this->getResponse($html);

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
