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

use TYPO3\CMS\Extbase\Persistence\Repository;
use Fab\Vidi\Tca\Tca;

/**
 * A repository for the Queue.
 */
class QueueRepository extends Repository {

	/**
	 * @var string
	 */
	protected $tableName = 'tx_messenger_domain_model_queue';

	/**
	 * @param array $message
	 * @throws \Exception
	 * @return int
	 */
	public function add($message){

		$values = array();
		$values['tstamp'] = $values['crdate'] = time(); // default values

		// Make sure fields are allowed for this table.
		$fields = Tca::table($this->tableName)->getFields();
		foreach ($message as $fieldName => $value) {
			if (in_array($fieldName, $fields)) {
				$values[$fieldName] = $value;
			}
		}

		$result = $this->getDatabaseConnection()->exec_INSERTquery($this->tableName, $values);
		if (!$result) {
			throw new \Exception('I could not queue the message.', 1389721932);
		}
		exit();
		return $this->getDatabaseConnection()->sql_insert_id();
	}

	/**
	 * Returns a pointer to the database.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
