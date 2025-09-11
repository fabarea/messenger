<?php

namespace Fab\Messenger\Controller;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class MessageDisplayController
 */
class MessageDisplayController extends ActionController
{
    public function showAction(): string
    {
        $result = 'Nothing to show!';
        $uuid = $this->request->getParsedBody()['uuid'] ?? $this->request->getQueryParams()['uuid'] ?? null;
        if ($this->isUuidValid($uuid)) {
            $source = (string)$this->request->getParsedBody()['source'] ?? $this->request->getQueryParams()['source'] ?? null;

            $message =
                $source === 'queue'
                    ? $this->getQueueRepository()->findByUuid($uuid)
                    : $this->getSentMessageRepository()->findByUuid($uuid);

            if (!empty($message)) {
                $result = $message['body'];
            }
        }
        return $result;
    }

    /**
     * @param $uuid
     * @return bool
     */
    public function isUuidValid($uuid): bool
    {
        $expression = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
        return preg_match($expression, (string)$uuid) === 1;
    }

    /**
     * @return QueueRepository
     */
    public function getQueueRepository(): QueueRepository
    {
        return GeneralUtility::makeInstance(QueueRepository::class);
    }

    /**
     * @return SentMessageRepository
     */
    public function getSentMessageRepository(): SentMessageRepository
    {
        return GeneralUtility::makeInstance(SentMessageRepository::class);
    }
}
