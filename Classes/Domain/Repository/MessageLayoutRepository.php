<?php
namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 *
 * @todo check how to handle language flag.
 */
class MessageLayoutRepository extends Repository
{

    /**
     * Finds an object given a qualifier name.
     *
     * @param string $qualifier
     * @return object|NULL
     * @api
     */
    public function findByQualifier($qualifier)
    {
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
//	 * @return \Fab\Messenger\Domain\Model\MessageLayout or NULL if no Layout object is found
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
//			$language = \Fab\Messenger\Utility\Context::getInstance()->getLanguage();
//			$records = tx_overlays::overlayRecordSet($tableName, $records, intval($language));
//		}
//		$layoutObject = NULL;
//		if (!empty($records[0])) {
//			$layoutObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Fab\Messenger\Domain\Model\MessageLayout', $records[0]);
//		}
//		return $layoutObject;
//	}
}
