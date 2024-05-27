<?php

namespace Fab\Messenger\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;

// the module template will be initialized in handleRequest()
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

#[AsController]
final class AdminModuleController extends ActionController
{
    public function __construct(protected readonly ModuleTemplateFactory $moduleTemplateFactory)
    {
        // ...
    }

    public function indexAction(): ResponseInterface
    {
        $this->view->assign('someVar', 'someContent');
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
