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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A repository for handling sent message
 */
class SentMessageRepository
{

    /**
     * @var string
     */
    protected $tableName = 'tx_messenger_domain_model_sentmessage';

    /**
     * @param array $message
     * @throws \RuntimeException
     * @return int
     */
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
            if (in_array($fieldName, $fields, true)) {
                $values[$fieldName] = $value;
            }
        }

        $query = $this->getQueryBuilder();
        $query->insert($this->tableName)
            ->values($values);

        $result = $query->execute();
        if (!$result) {
            throw new \RuntimeException('I could not save the message as "sent message"', 1389721852);
        }
        return $result;
    }

    /**
     * @param integer $uid
     * @return array
     */
    public function findByUid(int $uid): array
    {
        $query = $this->getQueryBuilder();
        $query->select('*')
            ->from($this->tableName)
            ->where(
                $this->getQueryBuilder()->expr()->eq(
                    'uuid',
                    $this->getQueryBuilder()->expr()->literal($uid)
                )
            );

        $messages = $query->execute()->fetch();

        return is_array($messages) ? $messages : [];
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function findByUuid(string $uuid): array
    {
        $query = $this->getQueryBuilder();
        $query->select('*')
            ->from($this->tableName)
            ->where(
                $this->getQueryBuilder()->expr()->eq(
                    'uuid',
                    $this->getQueryBuilder()->expr()->literal($uuid)
                )
            );

        $messages = $query->execute()->fetch();

        return is_array($messages) ? $messages : [];
    }

    /**
     * @param int $days
     * @return array
     */
    public function findOlderThanDays(int $days): array
    {
        $time = time() - ($days * 86400);
        $query = $this->getQueryBuilder();
        $query->select('*')
            ->from($this->tableName)
            ->where(
                'crdate < ' . $time
            );

        $messages = $query->execute()->fetchAll();
        return is_array($messages) ? $messages : [];
    }

    /**
     * @param int $days
     * @return int
     */
    public function removeOlderThanDays(int $days): int
    {
        $time = time() - ($days * 86400);

        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)
            ->where('crdate < ' . $time);

        return $query->execute();
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

}
