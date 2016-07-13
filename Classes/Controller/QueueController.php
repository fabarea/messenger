<?php
namespace Fab\Messenger\Controller;

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

/**
 * A controller for managing the queue.
 */
class QueueController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \Fab\Messenger\Domain\Repository\QueueRepository
     * @inject
     */
    protected $queueRepository;

    /**
     * Fetch message from the queue and send them.
     *
     * @return void
     */
    public function processAction()
    {

        // @todo get this limit by configuration.
        $limit = 100;
        $queuedMessages = $this->queueRepository->fetch($limit);

        foreach ($queuedMessages as $queuedMessage) {

            /** @var \Fab\Messenger\Domain\Model\Message $message */
            $message = $this->objectManager->get('Fab\Messenger\Domain\Model\Message');
            // @todo
            $message->hydrate($queuedMessage);
            $message->send();
        }
    }
}
