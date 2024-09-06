<?php

namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Utility\Algorithms;
use Fab\Vidi\Tca\Tca;
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

        $messages = $query->execute()->fetchOne();

        return is_array($messages) ? $messages : [];
    }

    /**
     * @return object|QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($this->tableName);
    }

    public function findByUids(array $uids): array
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where($this->getQueryBuilder()->expr()->in('uid', $uids));

        return $query->execute()->fetchAllAssociative();
    }

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

        $messages = $query->execute()->fetch();

        return is_array($messages) ? $messages : [];
    }

    public function findOlderThanDays(int $days): array
    {
        $time = time() - $days * 86400;
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where('crdate < ' . $time);

        $messages = $query->execute()->fetchAll();
        return is_array($messages) ? $messages : [];
    }

    public function removeOlderThanDays(int $days): int
    {
        $time = time() - $days * 86400;

        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where('crdate < ' . $time);

        return $query->execute();
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
            $queryBuilder->where($queryBuilder->expr()->orX(...$constraints));
        }

        # We handle the sorting
        $queryBuilder->addOrderBy(key($orderings), current($orderings));

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    public function add(array $message): int
    {
        $values = [];
        $values['crdate'] = time();
        $values['sent_time'] = time();

        // Add uuid info is not available
        if (empty($message['uuid'])) {
            $message['uuid'] = Algorithms::generateUUID();
        }

        // Make sure fields are allowed for this table.
        $fields = Tca::table($this->tableName)->getFields();
        foreach ($message as $fieldName => $value) {
            if (in_array($fieldName, $fields, true) && is_string($value)) {
                $values[$fieldName] = $value;
            }
        }

        $query = $this->getQueryBuilder();
        $query->insert($this->tableName)->values($values);

        $result = $query->execute();
        if (!$result) {
            throw new RuntimeException('I could not save the message as "sent message"', 1_389_721_852);
        }
        return $result;
    }

    public function findPendingMessages(int $limit): array
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where('scheduled_distribution_time < ' . time())
            ->setMaxResults($limit);

        $messages = $query->execute()->fetchAll();

        return is_array($messages) ? $messages : [];
    }

    public function remove(array $message): int
    {
        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where('uid = ' . $message['uid']);

        return $query->execute();
    }

    public function update(array $message): int
    {
        $query = $this->getQueryBuilder();
        $query->update($this->tableName)->where('uid = ' . $message['uid']);

        foreach ($message as $field => $value) {
            $query->set($field, $value);
        }
        return $query->execute();
    }
}
