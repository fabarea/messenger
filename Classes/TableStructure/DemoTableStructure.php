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
 * A demo table structure.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  media
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_TableStructure_DemoTableStructure implements Tx_Messenger_Interface_TableStructureInterface {

	/**
	 * @var array
	 */
	protected $users = array();

	/**
	 * @var array
	 */
	protected $tableHeader = array(
		array(
			'propertyName' => 'firstName',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:first_name',
		),
		array(
			'propertyName' => 'lastName',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:last_name',
		),
		array(
			'propertyName' => 'email',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:email',
		),
	);

	/**
	 * Constructor
	 *
	 * @return Tx_Messenger_TableStructure_DemoTableStructure
	 */
	public function __construct(){
		foreach (array(1, 2, 3, 4) as $uid) {
			$this->users[] = array(
				'uid' => $uid,
				'firstName' => uniqid('first'),
				'lastName' => uniqid('last'),
				'email' => uniqid('email@asdf.com'),
			);
		}
	}

	/**
	 * Returns a set of users.
	 *
	 * @return array
	 */
	public function getUsers() {
		return $this->users;
	}

	/**
	 * Get headers for the table.
	 *
	 * @return array
	 */
	public function getTableHeaders() {
		return $this->tableHeader;
	}
}

?>