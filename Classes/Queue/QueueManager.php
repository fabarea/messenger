<?php

namespace Fab\Messenger\Queue;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Exception\InvalidEmailFormatException;
use Fab\Messenger\Exception\WrongPluginConfigurationException;
use Fab\Messenger\Utility\Algorithms;
use Random\RandomException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class QueueManager
 */
class QueueManager
{
    /**
     * @throws RandomException
     * @throws DBALException
     * @throws Exception
     * @throws InvalidEmailFormatException
     * @throws WrongPluginConfigurationException
     */
    public function dequeue(int $itemsPerRun): array
    {
        $messengerMessages = $this->getQueueRepository()->findPendingMessages($itemsPerRun);

        $errorCount = $numberOfSentMessages = 0;
        /** @var array $messengerMessage */
        foreach ($messengerMessages as $messengerMessage) {
            /** @var Message $message */
            $message = GeneralUtility::makeInstance(Message::class);
            $message->setUuid(Algorithms::generateUUID());

            // Ensure body content is properly decoded
            $body = $messengerMessage['body'] ?? '';
            if (!empty($body)) {
                $originalBody = $body;

                $body = quoted_printable_decode($body);

                $previousBody = '';
                $decodeCount = 0;
                while ($body !== $previousBody && $decodeCount < 5) {
                    $previousBody = $body;
                    $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $decodeCount++;
                }

                $body = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $body);

                if (str_contains($body, '&lt;') || str_contains($body, '&gt;')) {
                    // Try additional decoding methods
                    $body = str_replace(['&lt;', '&gt;', '&amp;'], ['<', '>', '&'], $body);
                }
            }

            $message
                ->setBody($body)
                ->setSubject($messengerMessage['subject'])
                ->setSender($this->normalizeEmails($messengerMessage['sender']))
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
