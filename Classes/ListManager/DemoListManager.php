<?php
namespace Vanilla\Messenger\ListManager;
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
 * A demo list manager.
 */
class DemoListManager implements \Vanilla\Messenger\MessengerInterface\ListableInterface {

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
		array(
			'fieldName' => 'group',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:group',
		),
		array(
			'fieldName' => 'status',
			'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:status',
		),
	);

	/**
	 * @return \Vanilla\Messenger\ListManager\DemoListManager
	 */
	public function __construct(){
		foreach (array(1, 2, 3, 4) as $uid) {
			$this->records[$uid] = array(
				'uid' => $uid,
				'firstName' => 'first_name_' . $uid,
				'lastName' => 'last_name_' . $uid,
				'email' => sprintf('email_%s@test.com', $uid),
				'group' => $uid < 3 ? 'foo' : 'bar',
				'status' => $uid,
			);
			$this->records[$uid]['name'] = $this->records[$uid]['firstName'] . ' ' . $this->records[$uid]['lastName'];
		}
	}

	/**
	 * Returns a set of recipients.
	 * Notice, it is a very "cheap" algorithm for filtering a set of data for demo purposes
	 *
	 * @param \Vanilla\Messenger\QueryElement\Matcher $matcher
	 * @param \Vanilla\Messenger\QueryElement\Order $order
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function findBy(\Vanilla\Messenger\QueryElement\Matcher $matcher = NULL, \Vanilla\Messenger\QueryElement\Order $order = NULL, $limit = NULL, $offset = NULL) {

		$records = $this->records;
		$recordSet1 = $recordSet2 = $recordSet3 = array(1, 2, 3, 4);

		// Search for uid having match
		if (count($matcher->getMatches()) > 0) {
			if (in_array('status', array_keys($matcher->getMatches()))) {
				$recordSet1 = array();
			}
			if (in_array('group', array_keys($matcher->getMatches()))) {
				$recordSet2 = array();
			}
			foreach ($this->records as $record) {
				foreach ($matcher->getMatches() as $key => $value) {
					if ($key == 'status' && $record['status'] == $value) {
						$recordSet1[] = $record['uid'];
					} elseif ($key == 'group' && $record['group'] == $value) {
						$recordSet2[] = $record['uid'];
					}
				}
			}
		}

		// Search for uid having search term
		if ($matcher->getSearchTerm() !== '') {
			$recordSet3 = array();

			foreach ($this->records as $record) {
				if (preg_match('/' . $matcher->getSearchTerm() . '/isU', $record['firstName'])
					|| preg_match('/' . $matcher->getSearchTerm() . '/isU', $record['lastName'])
					|| preg_match('/' . $matcher->getSearchTerm() . '/isU', $record['email']))
				{
					$recordSet3[] = $record['uid'];
				}
			}
		}

		// Merge arrays
		if (count($matcher->getMatches()) > 0 || $matcher->getSearchTerm() !== '') {
			$uids = array_intersect($recordSet1, $recordSet2, $recordSet3);
			$records = array();
			foreach ($uids as $uid) {
				foreach ($this->records as $record) {
					if ($uid == $record['uid']) {
						$records[] = $record;
					}
				}
			}
		}
		return $records;
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
	 * @throws \Vanilla\Messenger\Exception\MissingKeyInArrayException
	 * @param int $uid an identifier for the record.
	 * @return array
	 */
	public function findByUid($uid) {
		if (empty($this->records[$uid])) {
			throw new \Vanilla\Messenger\Exception\MissingKeyInArrayException(sprintf('Uid does not exist: "%s"', $uid), 1357807844);
		}
		return $this->records[$uid];
	}

	/**
	 * @return array
	 */
	public function getMapping() {
		return array(
			'email' => 'email',
			'name' => 'name',
		);
	}

	/**
	 * Get list of possible filters.
	 * This must be an associative array containing the name of the filter as key and the values as filter
	 * array('group' => array('values' => 'foo', 'bar'));
	 *
	 * @return array
	 */
	public function getFilters() {
		return array(
			'group' => array(
				'values' => array(
					'?' => sprintf('%s %s',
						\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('select', 'messenger'),
						\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('group', 'messenger')
					),
					'foo' => 'foo',
					'bar' => 'bar',
				),
			),
			'status' => array(
				// key "label" is not used for now but could be in the future.
				#'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:status',
				'values' => array(
					'?' => sprintf('%s %s',
						\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('select', 'messenger'),
						\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('status', 'messenger')
					),
					1 => 'status 1',
					2 => 'status 2',
					3 => 'status 3',
					4 => 'status 4',
				),
			),
		);
	}
}

?>