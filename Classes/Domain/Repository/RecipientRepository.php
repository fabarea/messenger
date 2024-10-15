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
use Fab\Messenger\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Localization\LanguageService;

class RecipientRepository extends AbstractContentRepository
{
    protected string $tableName = '';

    public function __construct()
    {
        $this->tableName = ConfigurationUtility::getInstance()->get('recipient_data_type');
    }

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
        if ($orderings === []) {
            $orderings = ['uid' => 'ASC'];
        }
        # We handle the sorting
        $queryBuilder->addOrderBy(key($orderings), current($orderings));

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function findAll(): array
    {
        $query = $this->getQueryBuilder();
        $query->select('uid')->from($this->tableName);
        return $query->execute()->fetchAllAssociative();
    }

    /**
     * @throws DBALException
     * @throws Exception
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
     */
    public function deleteAllAction(): void
    {
        $this->getQueryBuilder()
            ->delete($this->tableName)
            ->execute();
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function exists(string $email): bool
    {
        $query = $this->getQueryBuilder();
        $record = $query
            ->select('*')
            ->from($this->tableName)
            ->where(
                $this->getQueryBuilder()
                    ->expr()
                    ->eq('email', $this->getQueryBuilder()->expr()->literal($email)),
            )
            ->execute()
            ->fetchAllAssociative();
        return !empty($record);
    }

    /**
     * @throws DBALException
     */
    public function insert(array $values): bool
    {
        $result = $this->getQueryBuilder()
            ->insert($this->tableName)
            ->values($values)
            ->execute();
        return (bool) $result;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
