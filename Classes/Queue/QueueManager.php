<?php

namespace Fab\Messenger\Queue;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class QueueManager
 */
class QueueManager
{
    public function dequeue(int $itemsPerRun): array
    {
        $this->getLogger()->info(sprintf('Dequeue started with itemsPerRun=%d', $itemsPerRun));
        
        $messengerMessages = $this->getQueueRepository()->findPendingMessages($itemsPerRun);
        
        $this->getLogger()->info(sprintf('Found %d pending messages', count($messengerMessages)));

        $errorCount = $numberOfSentMessages = 0;
        /** @var array $messengerMessage */
        foreach ($messengerMessages as $messengerMessage) {
            try {
                $this->getLogger()->info(sprintf('Processing message uid=%d', $messengerMessage['uid']));
                
                /** @var Message $message */
                $message = unserialize($messengerMessage['message_serialized'], ['allowed_classes' => true]);
                
                if ($message === false) {
                    throw new \RuntimeException('Failed to unserialize message');
                }

                $this->getLogger()->info(sprintf('Message uid=%d unserialized successfully, sending...', $messengerMessage['uid']));

                $isSent = $message->send();
                if ($isSent) {
                    $numberOfSentMessages++;
                    $this->getQueueRepository()->remove($messengerMessage);
                    $this->getLogger()->info(sprintf('Message uid=%d sent successfully', $messengerMessage['uid']));
                } else {
                    $errorCount++;
                    ++$messengerMessage['error_count'];
                    $this->getQueueRepository()->update($messengerMessage);
                    $this->getLogger()->warning(sprintf('Message uid=%d failed to send', $messengerMessage['uid']));
                }
            } catch (\Throwable $e) {
                $errorCount++;
                $messengerMessage['error_count'] = ($messengerMessage['error_count'] ?? 0) + 1;
                $this->getQueueRepository()->update($messengerMessage);
                
                $this->getLogger()->error(
                    sprintf('Messenger dequeue failed for message uid=%d: %s', $messengerMessage['uid'], $e->getMessage()),
                    ['exception' => $e]
                );
            }
        }
        
        $this->getLogger()->info(sprintf('Dequeue completed: %d sent, %d errors', $numberOfSentMessages, $errorCount));

        return [
            'errorCount' => $errorCount,
            'numberOfSentMessages' => $numberOfSentMessages,
        ];
    }

    public function dequeueOne(int $queuedMessageIdentifier): bool
    {
        $isSent = false;

        $messengerMessage = $this->getQueueRepository()->findByUid($queuedMessageIdentifier);

        if ($messengerMessage) {
            /** @var Message $message */
            $message = unserialize($messengerMessage['message_serialized'], ['allowed_classes' => true]);
            $isSent = (bool)$message->send();

            if ($isSent) {
                $this->getQueueRepository()->remove($messengerMessage);
                // Note: Message->send() already adds to SentMessageRepository, so we don't add it again here
            } else {
                ++$messengerMessage['error_count'];
                $this->getQueueRepository()->update($messengerMessage);
            }

        }
        return $isSent;
    }

    /**
     * @return object|QueueRepository
     */
    protected function getQueueRepository(): QueueRepository
    {
        return GeneralUtility::makeInstance(QueueRepository::class);
    }

    /**
     * @return object|SentMessageRepository
     */
    protected function getSentMessageRepository(): SentMessageRepository
    {
        return GeneralUtility::makeInstance(SentMessageRepository::class);
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

}
