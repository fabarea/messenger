<?php
namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @todo check how to handle language flag.
 */
class MessageTemplateRepository extends Repository
{

    /**
     * Initialize Repository
     */
    public function initializeObject()
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(FALSE);
        $this->setDefaultQuerySettings($querySettings);
    }

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
//	 * Finds a template record by its identifier.
//	 *
//	 * @param string $identifier
//	 * @return \Fab\Messenger\Domain\Model\MessageTemplate or NULL if no Template object is found
//	 */
//	public function findByIdentifier($identifier) {
//
//		// Get the main record
//		$tableName = 'tx_messenger_domain_model_messagetemplate';
//		$clause = 'sys_language_uid = 0 AND deleted = 0 AND identifier = "' . $identifier . '"';
//		$records = $this->databaseHandle->exec_SELECTgetRows('*', $tableName, $clause);
//
//		// Translates record and create the Template object
//		if (class_exists('tx_overlays')) {
//			$language = \Fab\Messenger\Utility\Context::getInstance()->getLanguage();
//			$records = tx_overlays::overlayRecordSet($tableName, $records, intval($language));
//		}
//		$templateObject = NULL;
//		if (! empty($records[0])) {
//			$templateObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Fab\Messenger\Domain\Model\MessageTemplate', $records[0]);
//		}
//		return $templateObject;
//	}
}
