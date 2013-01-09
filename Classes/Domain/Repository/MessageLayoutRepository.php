<?php

/***************************************************************
 *  Copyright notice
 *  (c) 2012 Fabien Udriot <fudriot@cobweb.ch>, Cobweb
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @package messenger
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @todo implement me using the Extbase persistence approach.
 * @todo check how to handle language flag.
 */
class Tx_Messenger_Domain_Repository_MessageLayoutRepository {

	/**
	 * @var t3lib_DB
	 */
	protected $databaseHandle;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->databaseHandle = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Finds a layout record by its identifier.
	 *
	 * @param string $identifier
	 * @return Tx_Messenger_Domain_Model_MessageLayout or NULL if no Layout object is found
	 */
	public function findByIdentifier($identifier) {

		// Get the main record
		$tableName = 'tx_messenger_domain_model_messagelayout';
		$clause = 'sys_language_uid = 0 AND deleted = 0 AND identifier = "' . $identifier . '"';
		$records = $this->databaseHandle->exec_SELECTgetRows('*', $tableName, $clause);

		// Translates record and create the Layout object
		if (class_exists('tx_overlays')) {
			$language = Tx_Messenger_Utility_Context::getInstance()->getLanguage();
			$records = tx_overlays::overlayRecordSet($tableName, $records, intval($language));
		}
		$layoutObject = NULL;
		if (!empty($records[0])) {
			$layoutObject = t3lib_div::makeInstance('Tx_Messenger_Domain_Model_MessageLayout', $records[0]);
		}
		return $layoutObject;
	}
}

?>