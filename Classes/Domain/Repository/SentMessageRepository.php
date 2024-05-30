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
use Fab\Vidi\Tca\Tca;
use RuntimeException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class SentMessageRepository extends AbstractContentRepository
{
    protected string $tableName = 'tx_messenger_domain_model_sentmessage';

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

        $messages = $query->execute()->fetch();

        return is_array($messages) ? $messages : [];
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

    public function findByDemand(array $demand = [], $orderings = []): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->orderBy('uid', 'ASC');

        // get inspiration from the other project to better handle the order and directions

        $queryBuilder->getRestrictions()->removeAll()->add($this->getDeletedRestriction());

        $this->addDemandConstraints($demand, $queryBuilder);
        return $queryBuilder->execute()->fetchAllAssociative();
    }
}
