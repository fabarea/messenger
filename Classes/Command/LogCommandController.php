<?php
namespace Fab\Messenger\Command;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class LogCommandController
 */
class LogCommandController extends CommandController
{

    /**
     * @var \Fab\Messenger\Domain\Repository\SentMessageRepository
     * @inject
     */
    protected $sendMessageRepository;

    /**
     * @param int $olderThanDays
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function cleanUpCommand($olderThanDays = 100)
    {
        $oldSentMessages = $this->sendMessageRepository->findOlderThanDays($olderThanDays);

        $numberOfOldSentMessage = count($oldSentMessages);

        if ($numberOfOldSentMessage > 0) {
            $this->sendMessageRepository->removeOlderThanDays($olderThanDays);
            $this->outputFormatted(
                'I removed %s entries older than %s days from the log of sent messages.',
                [$numberOfOldSentMessage, $olderThanDays]
            );
        }
    }

}
