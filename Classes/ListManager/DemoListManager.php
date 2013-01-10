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
 * A demo list manager.
 *
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_ListManager_DemoListManager implements Tx_Messenger_Interface_ListableInterface {

	/**
	 * @var array
	 */
	protected $records = array();

	/**
	 * @var array
	 */
	protected $fields = array(
		array(
			'fieldName' => 'firstName',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:first_name',
		),
		array(
			'fieldName' => 'lastName',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:last_name',
		),
		array(
			'fieldName' => 'email',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:email',
		),
	);

	/**
	 * Constructor
	 *
	 * @return Tx_Messenger_ListManager_DemoListManager
	 */
	public function __construct(){
		foreach (array(1, 2, 3, 4) as $uid) {
			$this->records[$uid] = array(
				'uid' => $uid,
				'firstName' => uniqid('first'),
				'lastName' => uniqid('last'),
				'email' => uniqid('email@asdf.com'),
			);
		}
	}

	/**
	 * Returns a set of recipients.
	 *
	 * @return array
	 */
	public function getRecords() {
		return $this->records;
	}

	/**
	 * Get the fields
	 *
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Get data about a particular record.
	 *
	 * @throws Tx_Messenger_Exception_MissingKeyInArrayException
	 * @param int $uid an identifier for the record.
	 * @return array
	 */
	public function getRecord($uid) {
		if (empty($this->records[$uid])) {
			throw new Tx_Messenger_Exception_MissingKeyInArrayException(sprintf('Uid does not exist: "%s"', $uid), 1357807844);
		}
		return $this->records[$uid];
	}
}

?>