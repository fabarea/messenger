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
 * Class dealing with table structure validation.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  media
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_Validator_TableStructureValidator {

	/**
	 * Validate a table structure.
	 *
	 * @param Tx_Messenger_Interface_TableStructureInterface $tableStructure
	 * @return void
	 */
	public function validate(Tx_Messenger_Interface_TableStructureInterface $tableStructure) {

		$tableHeaders = Tx_Messenger_TableStructure_Factory::getInstance()->getTableHeaders();
		$this->validateTableHeader($tableHeaders);
	}

	/**
	 * Validate the table header.
	 *
	 * @throws Tx_Messenger_Exception_MissingKeyInArrayException
	 * @param array $tableHeaders
	 */
	protected function validateTableHeader($tableHeaders) {
		if (empty($tableHeaders)) {
			throw new Tx_Messenger_Exception_EmptyArrayException('Empty array for table header', 1357656665);
		}

		foreach ($tableHeaders as $tableHeader) {

			if (empty($tableHeader['propertyName'])) {
				throw new Tx_Messenger_Exception_MissingKeyInArrayException('Missing key in table header "propertyName".', 1357656663);
			}

			if (empty($tableHeader['label'])) {
				throw new Tx_Messenger_Exception_MissingKeyInArrayException('Missing key in table header "label".', 1357656664);
			}


		}
	}

}

?>