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
 * A class dealing with object.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  messenger
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_Utility_Object {

	/**
	 * Convert an object to an array using different strategies. The first strategy who succeeds breaks the loop.
	 *
	 * @param object $object
	 * @return array
	 */
	public static function toArray($object) {
		$strategies = array('toArrayWithSpecialMethod', 'toArrayWithGetter', 'toArrayWithConvertingAttributes');

		foreach ($strategies as $strategy) {
			$values = self::$strategy($object);
			if (!empty($values)) {
				break;
			}
		}
		return $values;
	}

	/**
	 * Convert an object relying an internal method toArray() of the given object.
	 *
	 * @param object $object
	 * @return array
	 */
	protected static function toArrayWithSpecialMethod($object) {
		$values = array();
		if (method_exists($object, 'toArray')) {
			$values = $object->toArray();
		}
		return $values;
	}

	/**
	 * Convert an object using its getter methods.
	 *
	 * @param object $object
	 * @return array
	 */
	protected static function toArrayWithGetter($object) {
		$values = array();
		foreach (get_class_methods($object) as $method) {
			if (preg_match('/^get(.+)/is', $method, $match)) {
				$property = strtolower($match[1]);
				$values[$property] = call_user_func(array($object, $method));
			}
		}
		return $values;
	}

	/**
	 * Convert an object to array regardless of the visibility of its property.
	 *
	 * @param object $object
	 * @return array
	 */
	protected static function toArrayWithConvertingAttributes(& $object) {
		$clone = (array) $object;
		$result = array();

		while (list ($key, $value) = each($clone)) {
			$aux = explode("\0", $key);
			$newKey = $aux[count($aux) - 1];
			$result[$newKey] = $clone[$key];
		}
		return $result;
	}
}

?>