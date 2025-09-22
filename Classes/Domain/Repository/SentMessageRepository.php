<?php

namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Utility\Algorithms;
use Fab\Messenger\Utility\TcaFieldsUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class SentMessageRepository extends AbstractContentRepository
{
    protected string $tableName = 'tx_messenger_domain_model_sentmessage';

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

    public function findByUids(array $uids): array
    {
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where($this->getQueryBuilder()->expr()->in('uid', $uids));

        return $query->execute()->fetchAllAssociative();
    }

    public function findAll(): array
    {
        $query = $this->getQueryBuilder();
        $query->select('*')->from($this->tableName);

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

        $messages = $query->execute()->fetchAssociative();

        return is_array($messages) ? $messages : [];
    }

    public function findOlderThanDays(int $days): array
    {
        $time = time() - $days * 86400;
        $query = $this->getQueryBuilder();
        $query
            ->select('*')
            ->from($this->tableName)
            ->where('crdate <' . $time);

        $messages = $query->execute()->fetchAssociative();
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
        // We handle the sorting
        $queryBuilder->addOrderBy(key($orderings), current($orderings));

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    public function countByDemand(array $demand = []): int
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->count('uid')->from($this->tableName);
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

        return (int)$queryBuilder->execute()->fetchOne();
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
        $fields = TcaFieldsUtility::getFields($this->tableName);
        foreach ($message as $fieldName => $value) {
            if (in_array($fieldName, $fields, true) && is_string($value)) {
                $values[$fieldName] = $value;
            }
        }

        $query = $this->getQueryBuilder();
        $query->insert($this->tableName)->values($values);

        $result = $query->execute();
        if (!$result) {
            throw new \RuntimeException('I could not save the message as "sent message"', 1_389_721_852);
        }
        return $result;
    }

    public function removeByUid(int $uid): int
    {
        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where('uid = ' . $uid);
        return $query->execute();
    }

    public function deleteByUids(array $uids): int
    {
        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where($query->expr()->in('uid', $uids));
        return $query->execute();
    }
}
