<?php
namespace Vanilla\Messenger\Validator;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
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
 */
class ListManagerValidator {

	/**
	 * Validate a list manager.
	 *
	 * @param \Vanilla\Messenger\MessengerInterface\ListableInterface $listManager
	 * @return void
	 */
	public function validate(\Vanilla\Messenger\MessengerInterface\ListableInterface $listManager) {
		$this->validateFields($listManager);
		$this->validateMapping($listManager);

		// @todo find the right way for checking if recipients can be formatted array(email => name);
	}

	/**
	 * Validate the mapping.
	 *
	 * @param \Vanilla\Messenger\MessengerInterface\ListableInterface $listManager
	 * @throws \Vanilla\Messenger\Exception\MissingKeyInArrayException
	 */
	protected function validateMapping($listManager) {
		$mapping = $listManager->getMapping();

		foreach (array('email', 'name') as $value) {
			if (!isset($mapping[$value])) {
				throw new \Vanilla\Messenger\Exception\MissingKeyInArrayException(sprintf('mapping looks not correct, missing key "%s".', $value), 1370878760);
			}
		}
	}

	/**
	 * Validate the fields.
	 *
	 * @param \Vanilla\Messenger\MessengerInterface\ListableInterface $listManager
	 * @throws \Vanilla\Messenger\Exception\MissingKeyInArrayException
	 * @throws \Vanilla\Messenger\Exception\EmptyArrayException
	 */
	protected function validateFields($listManager) {
		$tableHeaders = $listManager->getFields();
		if (empty($tableHeaders)) {
			throw new \Vanilla\Messenger\Exception\EmptyArrayException('Empty array for fields', 1357656665);
		}

		foreach ($tableHeaders as $tableHeader) {

			if (empty($tableHeader['fieldName'])) {
				throw new \Vanilla\Messenger\Exception\MissingKeyInArrayException('fields look not correct, missing key "fieldName".', 1357656663);
			}

			if (empty($tableHeader['label'])) {
				throw new \Vanilla\Messenger\Exception\MissingKeyInArrayException('fields look not correct, missing key "label".', 1357656664);
			}
		}
	}

}

?>