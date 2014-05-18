<?php
namespace Vanilla\Messenger\ViewHelpers\Widget\Controller;

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
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController;

/**
 * Controller for the "show" widget.
 */
class ShowController extends AbstractWidgetController {

	/**
	 * A key / value structure representing the item.
	 *
	 * @var array
	 */
	protected $item;

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('dataType', $this->widgetConfiguration['dataType']);
		$this->view->assign('exclude', $this->widgetConfiguration['exclude']);
		$this->view->assign('displaySystemFields', $this->widgetConfiguration['displaySystemFields']);
		$this->view->assign('item', $this->item);
	}

	/**
	 * Special method for passing a reference to the item.
	 *
	 * @param array $item
	 */
	public function initializeItem(array $item) {
		$this->item = $item;
	}
}
