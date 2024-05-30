<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AdminModuleController extends ActionController
{
    protected SentMessageRepository $sentMessageRepository;
    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct()
    {
        $this->sentMessageRepository = GeneralUtility::makeInstance(SentMessageRepository::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
    }

    public function indexAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'messages' => [], // $this->sentMessageRepository->findByDemand(),
        ]);
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
