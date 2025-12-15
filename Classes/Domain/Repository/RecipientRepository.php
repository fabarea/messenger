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

        $messages = $query->executeQuery()->fetchAssociative();

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

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function findAll(): array
    {
        $query = $this->getQueryBuilder();
        $query->select('id')->from($this->tableName);
        return $query->executeQuery()->fetchAllAssociative();
    }

    public function findAllEmails(): array
    {
        $query = $this->getQueryBuilder();
        $query->select('email')->from($this->tableName);
        return $query->executeQuery()->fetchAllAssociative();
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

        return $query->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws DBALException
     */
    public function deleteAllAction(): void
    {
        $this->getQueryBuilder()->delete($this->tableName)->executeStatement();
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
            ->from($this->tableName)->where($this->getQueryBuilder()
            ->expr()
            ->eq('email', $this->getQueryBuilder()->expr()->literal($email)))->executeQuery()
            ->fetchAllAssociative();
        return !empty($record);
    }

    /**
     * @throws DBALException
     */
    public function insert(array $values): bool
    {
        // Add required TYPO3 fields for fe_users table
        $values['tstamp'] = time();
        $values['crdate'] = time();

        // Set pid if not already set (required for TYPO3)
        if (!isset($values['pid'])) {
            $values['pid'] = 0; // Default to root page
        }

        $result = $this->getQueryBuilder()
            ->insert($this->tableName)->values($values)->executeStatement();
        return (bool)$result;
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
