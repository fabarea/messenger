<?php
namespace TYPO3\CMS\Messenger\MessengerInterface;
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
interface ListableInterface {

	/**
	 * Get fields being displayed in the list.
	 *
	 * @return array
	 */
	public function getFields();

	/**
	 * Returns a set of records.
	 *
	 * @param \TYPO3\CMS\Messenger\QueryElement\Matcher $matcher
	 * @param \TYPO3\CMS\Messenger\QueryElement\Order $order
	 * @param int $limit
	 * @param int $offset
	 * @return array|Tx_Extbase_Persistence_QueryResultInterface
	 */
	public function findBy(\TYPO3\CMS\Messenger\QueryElement\Matcher $matcher = NULL, \TYPO3\CMS\Messenger\QueryElement\Order $order = NULL, $limit = NULL, $offset = NULL);

	/**
	 * Get data about a particular record.
	 *
	 * @param mixed $identifier an identifier for the record.
	 * @return array|Tx_Extbase_Persistence_QueryResultInterface
	 */
	public function findByUid($identifier);

	/**
	 * Return mapping info between the expected property of messenger and the one of your model.
	 * The value of example below corresponds to property of your model.
	 * Example:
	 *
	 * <pre>
	 * array(
	 *   'email' => 'email',
	 *  ' name' => 'name',
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public function getMapping();

	/**
	 * Get list of possible filters.
	 * This must be an associative array containing the name of the filter as key and the values as filter.
	 *
	 * array('group' => array('values' => 'foo', 'bar'));
	 *
	 * @return array
	 */
	public function getFilters();

}
?>