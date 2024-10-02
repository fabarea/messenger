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
use Fab\Messenger\Utility\Algorithms;
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
            /** @var Message $message */
            //$message = unserialize($messengerMessage['message_serialized'], ['allowed_classes' => true]);

            $message = GeneralUtility::makeInstance(Message::class);
            $message->setUuid(Algorithms::generateUUID());
            $message
                ->setBody($messengerMessage['body'] ?? '')
                ->setSubject($messengerMessage['subject'])
                ->setSender($this->normalizeEmails($messengerMessage['sender']))
                ->parseToMarkdown(true)
                ->setTo($this->normalizeEmails($messengerMessage['recipient']));
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
            'numberOfSentMessages' => $numberOfSentMessages,
        ];
    }

    /**
     * @return QueueRepository
     */
    protected function getQueueRepository(): QueueRepository
    {
        return GeneralUtility::makeInstance(QueueRepository::class);
    }

    protected function normalizeEmails(string $listOfFormattedEmails): array
    {
        $normalizedEmails = [];
        $formattedEmails = GeneralUtility::trimExplode(',', $listOfFormattedEmails);
        foreach ($formattedEmails as $formattedEmail) {
            $formattedEmail = trim($formattedEmail);
            if (preg_match('/^(.*) <(.*)>$/isU', $formattedEmail, $matches)) {
                if (count($matches) === 3) {
                    $normalizedEmails[$matches[2]] = $matches[1];
                }
            } else {
                if (filter_var($formattedEmail, FILTER_VALIDATE_EMAIL)) {
                    $normalizedEmails[$formattedEmail] = 'Unknown Name';
                }
            }
        }
        return $normalizedEmails;
    }

    /**
     * @return SentMessageRepository
     */
    protected function getSentMessageRepository(): SentMessageRepository
    {
        return GeneralUtility::makeInstance(SentMessageRepository::class);
    }

    public function dequeueOne(int $queuedMessageIdentifier): bool
    {
        $isSent = false;

        $messengerMessage = $this->getQueueRepository()->findByUid($queuedMessageIdentifier);

        if ($messengerMessage) {
            /** @var Message $message */
            $message = unserialize($messengerMessage['message_serialized'], ['allowed_classes' => true]);
            $isSent = (bool) $message->send();

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
}
