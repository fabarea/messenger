<?php
namespace Fab\Messenger\View\Button;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\View\Uri\EditUri;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\View\AbstractComponentView;
use Fab\Vidi\Domain\Model\Content;

/**
 * View which renders a "send" button to be placed in the grid.
 */
class SendButton extends AbstractComponentView
{

    /**
     * Renders a "edit" button to be placed in the grid.
     *
     * @param Content $object
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render(Content $object = NULL)
    {
        $editUri = $this->getUriRenderer()->render($object);

        return $this->makeLinkButton()
            ->setHref($editUri)
            ->setDataAttributes([
                'uid' => $object->getUid(),
                'toggle' => 'tooltip',
            ])
            ->setClasses('btn-edit')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:edit'))
            ->setIcon($this->getIconFactory()->getIcon('actions-document-open', Icon::SIZE_SMALL))
            ->render();
    }

    /**
     * @return \Fab\Vidi\View\Uri\EditUri
     * @throws \InvalidArgumentException
     */
    protected function getUriRenderer()
    {
        return GeneralUtility::makeInstance(EditUri::class);
    }

}
