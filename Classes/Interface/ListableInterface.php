<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@gebruederheitz.de>, Gebruederheitz
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * @package messenger
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
interface Tx_Messenger_Interface_ListableInterface {

	/**
	 * Get fields being displayed in the list.
	 *
	 * @return array
	 */
	public function getFields();

	/**
	 * Returns a set of records.
	 *
	 * @param Tx_Messenger_QueryElement_Matcher $matcher
	 * @param Tx_Messenger_QueryElement_Order $order
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 */
	public function findBy(Tx_Messenger_QueryElement_Matcher $matcher = NULL, Tx_Messenger_QueryElement_Order $order = NULL, $limit = NULL, $offset = NULL);

	/**
	 * Get data about a particular record.
	 *
	 * @param mixed $identifier an identifier for the record.
	 * @return mixed
	 */
	public function findByUid($identifier);

	/**
	 * Return recipient info according to an identifier. The returned array must look like:
	 * array('email' => 'recipient name');
	 *
	 * @param mixed $identifier an identifier for the record.
	 * @return mixed
	 */
	public function getRecipientInfo($identifier);

	/**
	 * Get list of possible filters.
	 * This must be an associative array containing the name of the filter as key and the values as filter
	 *
	 * array('group' => array('foo', 'bar'));
	 *
	 * @return array
	 */
	public function getFilters();

}
?>