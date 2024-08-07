<?php

namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @todo check how to handle language flag.
 */
class MessageTemplateRepository extends AbstractContentRepository
{
    protected string $tableName = 'tx_messenger_domain_model_messagetemplate';
    protected QueryInterface $constraints;

    //    /**
    //     * Initialize Repository
    //     */
    //    public function initializeObject()
    //    {
    //        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
    //        $querySettings->setRespectStoragePage(false);
    //        $this->setDefaultQuerySettings($querySettings);
    //    }
    //
    //    /**
    //     * Finds an object given a qualifier name.
    //     *
    //     * @param string $qualifier
    //     * @return object|NULL
    //     * @api
    //     */
    //    public function findByQualifier($qualifier)
    //    {
    //        $query = $this->createQuery();
    //        $query->getQuerySettings()->setRespectSysLanguage(false);
    //        $query->getQuerySettings()->setRespectStoragePage(false);
    //        $object = $query->matching($query->equals('qualifier', $qualifier))->execute()->getFirst();
    //        return $object;
    //    }

    public function findByDemand(array $demand = [], array $orderings = [], int $offset = 0, int $limit = 0): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('*')->from($this->tableName);

        $constraints = [];
        foreach ($demand as $field => $value) {
            $constraints[] = $queryBuilder
                ->expr()
                ->like(
                    $field,
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($value) . '%'),
                );
        }
        if ($constraints) {
            $queryBuilder->where($queryBuilder->expr()->orX(...$constraints));
        }

        # We handle the sorting
        $queryBuilder->addOrderBy(key($orderings), current($orderings));

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->execute()->fetchAllAssociative();
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
