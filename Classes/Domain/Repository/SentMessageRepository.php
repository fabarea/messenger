<?php
namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tca\Tca;

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
    public function add(array $message)
    {
        $values = [];
        $values['crdate'] = time();
        $values['sent_time'] = time();

        // Make sure fields are allowed for this table.
        $fields = Tca::table($this->tableName)->getFields();
        foreach ($message as $fieldName => $value) {
            if (in_array($fieldName, $fields, true)) {
                $values[$fieldName] = $value;
            }
        }

        $result = $this->getDatabaseConnection()->exec_INSERTquery($this->tableName, $values);
        if (!$result) {
            throw new \RuntimeException('I could not save the message as "sent message"', 1389721852);
        }
        return $this->getDatabaseConnection()->sql_insert_id();
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
