<?php

namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractContentRepository implements SingletonInterface
{
    protected function addDemandConstraints(array $demand, QueryBuilder $queryBuilder): void
    {
        $expressions = [];
        foreach ($demand as $fieldName => $value) {
            if (is_numeric($value)) {
                $expressions[] = $queryBuilder->expr()->eq($fieldName, $value);
            } elseif (is_string($value)) {
                $expressions[] = $queryBuilder->expr()->eq($fieldName, $queryBuilder->expr()->literal($value));
            } elseif (is_array($value)) {
                $expressions[] = $queryBuilder->expr()->in($fieldName, $value);
            }
        }
        foreach ($expressions as $expression) {
            $queryBuilder->andWhere($expression);
        }
    }

    protected function getDeletedRestriction(): DeletedRestriction
    {
        return GeneralUtility::makeInstance(DeletedRestriction::class);
    }

    protected function getHiddenRestriction(): HiddenRestriction
    {
        return GeneralUtility::makeInstance(HiddenRestriction::class);
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($this->tableName);
    }
}
