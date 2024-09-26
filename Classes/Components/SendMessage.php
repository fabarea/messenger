<?php

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

namespace Fab\Messenger\Components;

use Fab\Messenger\Service\SenderProvider;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class SendMessage
{
    public function render(): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('messenger') . 'Resources/Private/Standalone/Forms/SentMessage.html',
        );
        $senders = GeneralUtility::makeInstance(SenderProvider::class)->getFormattedPossibleSenders();

        return $view->render([
            'senders' => $senders,
        ]);
    }
}
