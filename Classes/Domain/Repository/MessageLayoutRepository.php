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
use TYPO3\CMS\Core\Localization\LanguageService;

class MessageLayoutRepository extends AbstractContentRepository
{
    protected string $tableName = 'tx_messenger_domain_model_messagelayout';

    /**
     * @throws DBALException
     * @throws Exception
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

        $messages = $query->executeQuery()->fetchOne();

        return is_array($messages) ? $messages : [];
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

        $messages = $query->executeQuery()->fetchAssociative();

        return is_array($messages) ? $messages : [];
    }

    /**
     * @throws Exception
     * @throws DBALException
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
        // We handle the sorting
        $queryBuilder->addOrderBy(key($orderings), current($orderings));

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function findAll(): array
    {
        $query = $this->getQueryBuilder();
        $query->select('*')->from($this->tableName);

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function deleteByUids(array $uids): int
    {
        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where($query->expr()->in('uid', $uids));
        return $query->executeStatement();
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
