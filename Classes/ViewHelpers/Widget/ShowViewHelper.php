<?php
namespace Vanilla\Messenger\ViewHelpers\Widget;

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

use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;

/**
 * View helper which render a generic item from the array of markers.
 */
class ShowViewHelper extends AbstractWidgetViewHelper {

	/**
	 * @var \Vanilla\Messenger\ViewHelpers\Widget\Controller\ShowController
	 * @inject
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
	public function render($item, $dataType = '', $exclude = array(), $displaySystemFields = FALSE) {
		return $this->initiateSubRequest();
	}

}
