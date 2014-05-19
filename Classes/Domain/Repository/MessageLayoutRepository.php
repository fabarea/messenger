<?php
namespace Vanilla\Messenger\Domain\Repository;
/***************************************************************
 *  Copyright notice
 *  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
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
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 *
 * @todo check how to handle language flag.
 */
class MessageLayoutRepository extends Repository {

	/**
	 * Finds an object given a qualifier name.
	 *
	 * @param string $qualifier
	 * @return object|NULL
	 * @api
	 */
	public function findByQualifier($qualifier) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$object = $query
			->matching(
				$query->equals('qualifier', $qualifier)
			)
			->execute()
			->getFirst();
		return $object;
	}



//	/** @todo resolve overlays of record
//	 * Finds a layout record by its identifier.
//	 *
//	 * @param string $identifier
//	 * @return \Vanilla\Messenger\Domain\Model\MessageLayout or NULL if no Layout object is found
//	 */
//	public function findByIdentifier($identifier) {
//
//		// Get the main record
//		$tableName = 'tx_messenger_domain_model_messagelayout';
//		$clause = 'sys_language_uid = 0 AND deleted = 0 AND identifier = "' . $identifier . '"';
//		$records = $this->databaseHandle->exec_SELECTgetRows('*', $tableName, $clause);
//
//		// Translates record and create the Layout object
//		if (class_exists('tx_overlays')) {
//			$language = \Vanilla\Messenger\Utility\Context::getInstance()->getLanguage();
//			$records = tx_overlays::overlayRecordSet($tableName, $records, intval($language));
//		}
//		$layoutObject = NULL;
//		if (!empty($records[0])) {
//			$layoutObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Vanilla\Messenger\Domain\Model\MessageLayout', $records[0]);
//		}
//		return $layoutObject;
//	}
}
