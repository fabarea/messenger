<?php
namespace Fab\Messenger\ViewHelpers\Widget\Controller;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController;

/**
 * Controller for the "show" widget.
 */
class ShowController extends AbstractWidgetController
{

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('dataType', $this->widgetConfiguration['dataType']);
        $this->view->assign('exclude', $this->widgetConfiguration['exclude']);
        $this->view->assign('displaySystemFields', $this->widgetConfiguration['displaySystemFields']);
        $this->view->assign('item', $this->widgetConfiguration['item']);
    }
}
