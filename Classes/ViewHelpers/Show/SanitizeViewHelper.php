<?php
namespace Vanilla\Messenger\ViewHelpers\Show;
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which makes possible to traverse an object as for an associative array(key => value).
 */
class SanitizeViewHelper extends AbstractViewHelper {

	/**
	 * Return a traversable object as for an associative array(key => value).
	 *
	 * @param mixed $item
	 * @return string
	 */
	public function render($item) {
		$item = $this->makeItemTraversable($item);
		$item = $this->convertPropertiesToFields($item);
		return $item;
	}

	/**
	 * @param mixed $item
	 * @return string
	 */
	protected function convertPropertiesToFields($item) {
		$convertedItem = array();
		foreach ($item as $propertyName => $value) {
			$fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
			$convertedItem[$fieldName] = $value;
		}
		return $convertedItem;
	}

	/**
	 * Return a traversable object as for an associative array(key => value).
	 *
	 * @param mixed $item
	 * @return string
	 */
	protected function makeItemTraversable($item) {
		if ($item instanceof AbstractEntity) {
			$item = $item->_getProperties();
		}
		return $item;
	}

}
