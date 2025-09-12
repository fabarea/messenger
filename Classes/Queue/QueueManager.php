<?php

namespace Fab\Messenger\Queue;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class QueueManager
 */
class QueueManager
{

    public function dequeue(int $itemsPerRun): array
    {
        $messengerMessages = $this->getQueueRepository()->findPendingMessages($itemsPerRun);

        $errorCount = $numberOfSentMessages = 0;
        /** @var array $messengerMessage */
        foreach ($messengerMessages as $messengerMessage) {
            /** @var MailMessage $message */
            $message = unserialize($messengerMessage['message_serialized'], ['allowed_classes' => true]);

            $isSent = $message->send();
            if ($isSent) {
                $numberOfSentMessages++;
                $this->getQueueRepository()->remove($messengerMessage);
                $this->getSentMessageRepository()->add($messengerMessage);
            } else {
                $errorCount++;
                ++$messengerMessage['error_count'];
                $this->getQueueRepository()->update($messengerMessage);
            }
        }

        return [
            'errorCount' => $errorCount,
            'numberOfSentMessages' => $numberOfSentMessages
        ];
    }

    public function dequeueOne(int $queuedMessageIdentifier): bool
    {
        $isSent = false;

        $messengerMessage = $this->getQueueRepository()->findByUid($queuedMessageIdentifier);

        if ($messengerMessage) {
            /** @var MailMessage $message */
            $message = unserialize($messengerMessage['message_serialized'], ['allowed_classes' => true]);
            $isSent = (bool)$message->send();

            if ($isSent) {
                $this->getQueueRepository()->remove($messengerMessage);
                $this->getSentMessageRepository()->add($messengerMessage);
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

}
