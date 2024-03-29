<?php
namespace Fab\Messenger\ViewHelpers\Widget;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\ViewHelpers\Widget\Controller\ShowController;
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;
use TYPO3\CMS\Extbase\Annotation\Inject;

/**
 * View helper which render a generic item from the array of markers.
 */
class ShowViewHelper extends AbstractWidgetViewHelper
{

    /**
     * @var ShowController
     * @Inject
     */
    protected $controller;

    /**
     * Returns a generic view of an item.
     *
     * @param string $item corresponds to a name of a marker.
     * @param string $dataType the data type if item is an object, basically corresponds to a table name.
     * @param array $exclude excluded fields from the output
     * @param bool $displaySystemFields
     * @return string
     */
    public function render($item, $dataType = '', $exclude = [], $displaySystemFields = FALSE)
    {
        return $this->initiateSubRequest();
    }

}
