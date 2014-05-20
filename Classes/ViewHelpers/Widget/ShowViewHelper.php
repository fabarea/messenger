<?php
namespace Vanilla\Messenger\ViewHelpers\Widget;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
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
