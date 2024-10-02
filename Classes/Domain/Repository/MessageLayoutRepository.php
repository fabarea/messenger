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

        $messages = $query->execute()->fetchOne();

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

        return $query->execute()->fetchAllAssociative();
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

        $messages = $query->execute()->fetchAssociative();

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

        $messages = $query->execute()->fetchAllAssociative();
        return is_array($messages) ? $messages : [];
    }

    public function removeOlderThanDays(int $days): int
    {
        $time = time() - $days * 86400;

        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)->where('crdate < ' . $time);

        return $query->execute();
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

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
