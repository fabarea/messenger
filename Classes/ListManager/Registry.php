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
 * A registry for list manager.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  messenger
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_ListManager_Registry implements t3lib_Singleton {

	/**
	 * @var array
	 */
	protected $registry = array();


	/**
	 * Get an instance of a list manager interface
	 *
	 * @return Tx_Messenger_ListManager_Registry
	 */
	public static function getInstance() {
		return t3lib_div::makeInstance('Tx_Messenger_ListManager_Registry');
	}

	/**
	 * Adds a new list manager to the registry.
	 *
	 * @param string $listManager
	 * @param string $description
	 * @return void
	 */
	public function add($listManager, $description) {
		if (! $this->isRegistered($listManager)) {
			$this->registry[$listManager] = $description;
		}
	}

	/**
	 * Remove a list manager to the registry.
	 *
	 * @param string $listManager Extension key to be used
	 * @return void
	 */
	public function remove($listManager) {
		if ($this->isRegistered($listManager)) {
			unset($this->registry[$listManager]);
		}
	}

	/**
	 * Tells whether a list manager is registered or not.
	 *
	 * @param string $listManager Name of the table to be looked up
	 * @return boolean
	 */
	public function isRegistered($listManager) {
		return isset($this->registry[$listManager]) ? TRUE : FALSE;
	}

	/**
	 * Gets the list manager registry.
	 *
	 * @return array
	 */
	public function get() {
		return $this->registry;
	}

	/**
	 * Gets the number of items in the registry.
	 *
	 * @return array
	 */
	public function count() {
		return count($this->registry);
	}
}
?>