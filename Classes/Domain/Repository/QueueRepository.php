<?php
namespace Fab\Messenger\Domain\Repository;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Mail\MailMessage;
use Fab\Vidi\Tca\Tca;

/**
 * A repository for the Queue.
 */
class QueueRepository
{

    /**
     * @var string
     */
    protected $tableName = 'tx_messenger_domain_model_queue';

    /**
     * @param array $message
     * @return int
     * @throws \RuntimeException
     */
    public function add(array $message)
    {
        if (!$message['mail_message'] instanceof MailMessage) {
            throw new \RuntimeException('Please, make sure key "mail_message" is a valid mail message object', 1469694987);
        }

        $values = [];
        $values['crdate'] = time(); // default values
        $values['message_serialized'] = serialize($message['mail_message']);


        // Make sure fields are allowed for this table.
        $fields = Tca::table($this->tableName)->getFields();
        foreach ($message as $fieldName => $value) {
            if (in_array($fieldName, $fields, true)) {
                $values[$fieldName] = $value;
            }
        }

        $result = $this->getDatabaseConnection()->exec_INSERTquery($this->tableName, $values);
        if (!$result) {
            throw new \RuntimeException('I could not queue the message.', 1389721932);
        }
        return $this->getDatabaseConnection()->sql_insert_id();
    }

    /**
     * @param integer $limit
     * @return array
     * @throws \InvalidArgumentException
     */
    public function findPendingMessages($limit)
    {
        $clause = 'scheduled_distribution_time < ' . time();
        $messages = $this->getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            $this->tableName,
            $clause,
            '',
            '',
            $limit
        );

        return is_array($messages) ? $messages : [];
    }

    /**
     * @param array $message
     * @return array
     */
    public function remove($message)
    {
        $this->getDatabaseConnection()->exec_DELETEquery(
            $this->tableName,
            'uid = ' . $message['uid']
        );
    }

    /**
     * @param array $message
     * @return array
     */
    public function update($message)
    {
        $messageIdentifier = $message['uid'];
        unset($message['uid']);
        $this->getDatabaseConnection()->exec_UPDATEquery(
            $this->tableName,
            'uid = ' . $messageIdentifier,
            $message
        );
    }

    /**
     * Returns a pointer to the database.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
