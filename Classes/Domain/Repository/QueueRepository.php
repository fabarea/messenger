<?php

namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Fab\Messenger\Utility\Algorithms;
use Fab\Messenger\Utility\TcaFieldsUtility;
use Random\RandomException;
use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * A repository for the Queue.
 */
class QueueRepository extends AbstractContentRepository
{
    /**
     * @var string
     */
    protected string $tableName = 'tx_messenger_domain_model_queue';

    protected QueryInterface $constraints;

    /**
     * @throws Exception
     * @throws DBALException
     */
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

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($this->tableName);
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function findByUids(array $uids): array
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where($this->getQueryBuilder()->expr()->in('uid', $uids));

        return $query->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function findByUuid(string $uuid): array
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where(
                $this->getQueryBuilder()
                    ->expr()
                    ->eq('uuid', $this->getQueryBuilder()->expr()->literal($uuid)),
            );

        $messages = $query->executeQuery()->fetchAllAssociative();

        return is_array($messages) ? $messages : [];
    }

    public function findAll(): array
    {
        $query = $this->getQueryBuilder();
        $query->select('*')->from($this->tableName);

        return $query->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function findOlderThanDays(int $days): array
    {
        $time = time() - $days * 86400;
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where('crdate < ' . $time);

        $messages = $query->executeQuery()->fetchAllAssociative();
        return is_array($messages) ? $messages : [];
    }

    /**
     * @throws DBALException
     */
    public function removeOlderThanDays(int $days): int
    {
        $time = time() - $days * 86400;

        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where('crdate < ' . $time);

        return $query->executeStatement();
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function findByDemand(array $demand = [], array $orderings = [], int $offset = 0, int $limit = 0): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('*')->from($this->tableName);
        $constraints = [];

        if (!empty($demand['likes'])) {
            foreach ($demand['likes'] as $field => $value) {
                $constraints[] = $queryBuilder
                    ->expr()
                    ->like(
                        $field,
                        $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($value) . '%'),
                    );
            }
            $queryBuilder->where($queryBuilder->expr()->or(...$constraints));
        }
        if (!empty($demand['uids'])) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('uid', $demand['uids']));
        }

        if ($constraints) {
            $queryBuilder->where($queryBuilder->expr()->or(...$constraints));
        }
        if ($orderings === []) {
            $orderings = ['uid' => 'ASC'];
        }
        # We handle the sorting
        $queryBuilder->addOrderBy(key($orderings), current($orderings));

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws RandomException
     * @throws DBALException
     */
    public function add(array $message): int
    {
        $values = [];
        if (empty($message['uuid'])) {
            $message['uuid'] = Algorithms::generateUUID();
        }
        // Make sure fields are allowed for this table.
        $fields = TcaFieldsUtility::getFields($this->tableName);
        foreach ($message as $fieldName => $value) {
            if (in_array($fieldName, $fields, true) && is_string($value)) {
                $values[$fieldName] = $value;
            }
        }
        $query = $this->getQueryBuilder();
        $query->insert($this->tableName)->values($values);
        $result = $query->executeStatement();
        if (!$result) {
            throw new RuntimeException('I could not save the message as "sent message"', 1_389_721_852);
        }
        return $result;
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function findPendingMessages(int $limit): array
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where('scheduled_distribution_time < ' . time())
            ->setMaxResults($limit);

        $messages = $query->executeQuery()->fetchAllAssociative();

        return is_array($messages) ? $messages : [];
    }

    /**
     * @throws DBALException
     */
    public function remove(array $message): int
    {
        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where('uid = ' . $message['uid']);

        return $query->executeStatement();
    }

    /**
     * @throws DBALException
     */
    public function update(array $message): int
    {
        $query = $this->getQueryBuilder();
        $query->update($this->tableName)->where('uid = ' . $message['uid']);

        foreach ($message as $field => $value) {
            $query->set($field, $value);
        }
        return $query->executeStatement();
    }

    public function deleteByUids(array $uids): int
    {
        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where($query->expr()->in('uid', $uids));
        return $query->executeStatement();
    }
}
