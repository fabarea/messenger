<?php
namespace Fab\Messenger\View\MenuItem;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Module\MessengerModule;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use Fab\Vidi\View\AbstractComponentView;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View which renders a "send" item to be placed in the menu.
 */
class SendMenuItem extends AbstractComponentView
{

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render()
    {
        $this->loadRequireJsCode();
        $result = sprintf('<li><a href="%s" class="btn-bulk-send">%s %s</a></li>',
            $this->getBulkSendUri(),
            $this->getIconFactory()->getIcon('mimetypes-open-document-drawing', Icon::SIZE_SMALL),
            $this->getLanguageService()->sL('LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.message')
        );
        return $result;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getBulkSendUri()
    {

        $urlParameters = [
            MessengerModule::getParameterPrefix() => [
                'controller' => 'BackendMessage',
                'action' => 'compose',
            ],
        ];
        return BackendUtility::getModuleUrl(MessengerModule::getSignature(), $urlParameters);
    }

    /**
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function loadRequireJsCode()
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        $configuration['paths']['Fab/Messenger'] = '../typo3conf/ext/messenger/Resources/Public/JavaScript';
        $pageRenderer->addRequireJsConfiguration($configuration);
        $pageRenderer->loadRequireJsModule('Fab/Messenger/SendMenuItem');
    }
}
