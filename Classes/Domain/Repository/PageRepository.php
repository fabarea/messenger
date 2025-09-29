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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageRepository extends AbstractContentRepository
{
    protected string $tableName = 'pages';
    protected  ConnectionPool $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
     {
         $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
     }

    public function findByUids(array $uids): array
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where($this->getQueryBuilder()->expr()->in('uid', $uids));

        return $query->executeQuery()->fetchAllAssociative();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = $this->connectionPool;
        return $connectionPool->getQueryBuilderForTable($this->tableName);
    }

    public function findByUid(int $uid): array
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where(
                $this->getQueryBuilder()
                    ->expr()
                    ->eq('uid', $this->getQueryBuilder()->expr()->literal($uid)),
            );

        $messages = $query->executeQuery()->fetchAssociative();

        return is_array($messages) ? $messages : [];
    }

    public function findAll(): array
    {
        $query = $this->getQueryBuilder();
        $query->select('*')->from($this->tableName);

        return $query->executeQuery()->fetchAllAssociative();
    }

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
            $queryBuilder->where($queryBuilder->expr()->or(...$constraints));
        }
        if ($orderings === []) {
            $orderings = ['uid' => 'ASC'];
        }
        // We handle the sorting
        $queryBuilder->addOrderBy(key($orderings), current($orderings));

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function deleteByUids(array $uids): int
    {
        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where($query->expr()->in('uid', $uids));
        return $query->executeQuery();
    }
}
