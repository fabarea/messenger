<?php

namespace Fab\Messenger\Task;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Queue\QueueManager;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Log\LogManager;

class MessengerDequeueTask extends AbstractTask
{
    public int $itemsPerRun = 300;

    protected ?LoggerInterface $logger;

    public function __construct()
    {
        parent::__construct();
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    public function execute(): bool
    {
        try {
            $result = $this->getQueueManager()->dequeue($this->itemsPerRun);

            // Task succeeds if we processed messages, even if some had errors
            // Also succeed if there are no messages to process (empty queue)
            $totalProcessed = $result['errorCount'] + $result['numberOfSentMessages'];

            // Log the results for monitoring
            if ($totalProcessed === 0) {
                $this->logger->info('Messenger dequeue task completed successfully. No messages in queue to process.');
                return true;
            } elseif ($result['errorCount'] > 0) {
                $this->logger->warning(
                    sprintf(
                        'Messenger dequeue task completed with %d errors out of %d processed messages. %d messages sent successfully.',
                        $result['errorCount'],
                        $totalProcessed,
                        $result['numberOfSentMessages']
                    )
                );
            } else {
                $this->logger->info(
                    sprintf(
                        'Messenger dequeue task completed successfully. %d messages sent.',
                        $result['numberOfSentMessages']
                    )
                );
            }

            return true;

        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'Messenger dequeue task failed with exception: %s',
                    $e->getMessage()
                ),
                ['exception' => $e]
            );
            return false;
        }
    }

    /**
     * @return QueueManager
     */
    protected function getQueueManager(): QueueManager
    {
        return GeneralUtility::makeInstance(QueueManager::class);
    }
}
