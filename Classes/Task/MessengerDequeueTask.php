<?php

namespace Fab\Messenger\Task;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Queue\QueueManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class MessengerDequeueTask extends AbstractTask
{
    public int $itemsPerRun = 300;

    public function execute(): bool
    {
        $result = $this->getQueueManager()->dequeue($this->itemsPerRun);
        return $result['errorCount'] === 0;
    }

    /**
     * @return QueueManager
     */
    protected function getQueueManager(): QueueManager
    {
        return GeneralUtility::makeInstance(QueueManager::class);
    }
}
