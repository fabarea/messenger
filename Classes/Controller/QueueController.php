<?php
namespace Vanilla\Messenger\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A controller for managing the queue.
 */
class QueueController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \Vanilla\Messenger\Domain\Repository\QueueRepository
	 * @inject
	 */
	protected $queueRepository;

	/**
	 * Fetch message from the queue and send them.
	 *
	 * @return void
	 */
	public function processAction() {

		// @todo get this limit by configuration.
		$limit = 100;
		$queuedMessages = $this->queueRepository->fetch($limit);

		foreach ($queuedMessages as $queuedMessage) {

			/** @var \Vanilla\Messenger\Domain\Model\Message $message */
			$message = $this->objectManager->get('Vanilla\Messenger\Domain\Model\Message');
			// @todo
			$message->hydrate($queuedMessage);
			$message->send();
		}
	}
}
?>