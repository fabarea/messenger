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
 * Class dealing with list manager validation.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  messenger
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_Validator_TableStructureValidator {

	/**
	 * Validate a list manager.
	 *
	 * @param Tx_Messenger_Interface_ListableInterface $tableStructure
	 * @return void
	 */
	public function validate(Tx_Messenger_Interface_ListableInterface $tableStructure) {

		$tableHeaders = Tx_Messenger_ListManager_Factory::getInstance()->getFields();
		$this->validateFields($tableHeaders);
	}

	/**
	 * Validate the fields.
	 *
	 * @throws Tx_Messenger_Exception_MissingKeyInArrayException
	 * @param array $tableHeaders
	 */
	protected function validateFields($tableHeaders) {
		if (empty($tableHeaders)) {
			throw new Tx_Messenger_Exception_EmptyArrayException('Empty array for fields', 1357656665);
		}

		foreach ($tableHeaders as $tableHeader) {

			if (empty($tableHeader['fieldName'])) {
				throw new Tx_Messenger_Exception_MissingKeyInArrayException('Missing key in fields "fieldName ".', 1357656663);
			}

			if (empty($tableHeader['label'])) {
				throw new Tx_Messenger_Exception_MissingKeyInArrayException('Missing key in fields "label".', 1357656664);
			}
		}
	}

}

?>