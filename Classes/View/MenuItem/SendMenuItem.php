<?php
namespace Fab\Messenger\View\MenuItem;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Module\MessengerModule;
use Fab\Messenger\Utility\BackendUtility;
use InvalidArgumentException;
use TYPO3\CMS\Core\Imaging\Icon;
use Fab\Vidi\View\AbstractComponentView;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View which renders a "send" item to be placed in the menu.
 */
class SendMenuItem extends AbstractComponentView
{
    protected IconFactory $iconFactory;
    protected PageRenderer $pageRenderer;

    public function __construct(
        IconFactory $iconFactory,
        PageRenderer $pageRenderer
    ) {
        $this->iconFactory = $iconFactory;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function render()
    {
        $this->loadRequireJsCode();
        $result = sprintf('<li><a href="%s" class="dropdown-item btn-bulk-send">%s %s</a></li>',
            $this->getBulkSendUri(),
            $this->iconFactory->getIcon('mimetypes-open-document-drawing', Icon::SIZE_SMALL),
            $this->getLanguageService()->sL('LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.message')
        );
        return $result;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getBulkSendUri(): string
    {
        $urlParameters = [
            MessengerModule::getParameterPrefix() => [
                'controller' => 'BackendMessage',
                'action' => 'compose',
            ],
        ];

        $pid = $this->getModuleLoader()->getCurrentPid();
        if ($pid > 0) {
            $urlParameters = [
                MessengerModule::getParameterPrefix() => [
                    'pageId' => $pid,
                ],
            ];

        }
        return BackendUtility::getModuleUrl(MessengerModule::getSignature(), $urlParameters);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function loadRequireJsCode(): void
    {
        $configuration = [];
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        $configuration['paths']['TYPO3/CMS/Messenger'] = '../typo3conf/ext/messenger/Resources/Public/JavaScript';
        $pageRenderer->addRequireJsConfiguration($configuration);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Messenger/SendMenuItem');
    }
}
