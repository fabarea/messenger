<?php
namespace Fab\Messenger\ViewHelpers\Widget\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
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
