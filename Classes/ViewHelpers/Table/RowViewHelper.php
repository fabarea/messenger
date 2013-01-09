<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012
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

/**
 * View helper that a render a table row.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  messenger
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_ViewHelpers_Table_RowViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Render a row of a table given an object as input.
	 *
	 * @param Object $object
	 * @return string
	 */
	public function render($object) {

		$result = '';
		$template = '<td %s %s>%s</td>';
		$tableHeaders = Tx_Messenger_ListManager_Factory::getInstance()->getFields();

		foreach ($tableHeaders as $tableHeader) {
			if (is_array($object)) {
				$value = $this->getValueForArray($object, $tableHeader['fieldName']);
			} elseif (is_object($object)) {
				$value = $this->getValueForObject($object, $tableHeader['fieldName']);
			}

			$result .= sprintf($template . PHP_EOL,
				$this->getStyleAttribute($tableHeader),
				empty($tableHeader['class']) ? '' : $tableHeader['class'],
				$value
			);
		}
		return $result;
	}

	/**
	 * Get a value for an array given a key.
	 *
	 * @throws Tx_Messenger_Exception_UnknownMethodException
	 * @param array $array
	 * @param string $key
	 * @return string
	 */
	protected function getValueForArray($array, $key) {
		if (empty($array[$key])) {
			throw new Tx_Messenger_Exception_UnknownMethodException('Array does not contain a key: ' . $key, 1357668817);
		}
		return $array[$key];
	}

	/**
	 * Get a value for an object given a property name.
	 *
	 * @throws Tx_Messenger_Exception_UnknownMethodException
	 * @param Object $object
	 * @param string $propertyName
	 * @return string
	 */
	protected function getValueForObject($object, $propertyName) {
		$getter = 'get' . ucfirst($propertyName);
		if (! method_exists($object, $getter)) {
			throw new Tx_Messenger_Exception_UnknownMethodException('Object does not have method: ' . $getter, 1357668816);
		}
		return $object->$getter;
	}

	/**
	 * Get a style attribute according to header configuration.
	 *
	 * @param array $tableHeader
	 * @return string
	 */
	protected function getStyleAttribute(array $tableHeader = array()) {
		$result = '';
		if (!empty($tableHeader['style'])) {
			$result = rtrim($tableHeader['style'], ';') . ';';
		}

		if (!empty($tableHeader['width'])) {
			$result .= 'width: ' . $tableHeader['width'];
		}
		return sprintf('style="%s"', $result);
	}
}

?>