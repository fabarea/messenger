<?php
namespace Fab\Messenger\Command;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Controller
 */
class MessageQueueCommandController extends CommandController
{

    /**
     * @var \Fab\Messenger\Domain\Repository\QueueRepository
     * @inject
     */
    protected $queueRepository;

    /**
     * @var \Fab\Messenger\Domain\Repository\SentMessageRepository
     * @inject
     */
    protected $sendMessageRepository;

    /**
     * @param int $itemsPerRun
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function dequeueCommand($itemsPerRun = 100)
    {
        $pendingMessages = $this->queueRepository->findPendingMessages($itemsPerRun);

        $errorCount = 0;
        foreach ($pendingMessages as $pendingMessage) {
            /** @var MailMessage $message */
            $message = unserialize($pendingMessage['message_serialized']);

            if ($pendingMessage['redirect_email']) {

                // hack! Transmit in the message subject the application context to apply possible email redirect.
                $dirtySubject = sprintf(
                    '%s---%s###REDIRECT###%s',
                    $pendingMessage['context'],
                    $pendingMessage['redirect_email'],
                    $message->getSubject()
                );
                $message->setSubject($dirtySubject);
            }

            $isSent = $message->send();
            if ($isSent) {
                $this->queueRepository->remove($pendingMessage);
                $this->sendMessageRepository->add($pendingMessage);
            } else {
                $errorCount++;
                $pendingMessage['error_count'] += 1;
                $this->queueRepository->update($pendingMessage);
            }
        }

        if ($errorCount > 0) {
            $this->outputFormatted('I encountered %s problem while processing the message queue.', [$errorCount]);
        }
    }

}
