<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Repository\PageRepository;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Fab\Messenger\Service\SenderProvider;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->getTemplatePaths()->fillDefaultsByPackageName('messenger');
        $view->setTemplatePathAndFilename('EXT:messenger/Resources/Private/Standalone/Forms/SentMessage.html');
        $pageContent = $this->pageRepository->findByUid($this->getPageId());
        $view->assignMultiple([
            'senders' => GeneralUtility::makeInstance(SenderProvider::class)->getFormattedPossibleSenders(),
            'title' => $pageContent['title'] ?? ''
        ]);
        return $this->getResponse($view->render());
    }
}
