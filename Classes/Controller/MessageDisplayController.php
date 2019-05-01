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

    /**
     * @return string
     */
    public function showAction(): string
    {

        $result = 'Nothing to show!';
        $uuid = (string)GeneralUtility::_GP('uuid');
        if ($this->isUuidValid($uuid)) {
            $source = (string)GeneralUtility::_GP('source');

            $message = $source === 'queue'
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
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) === 1;
    }

    /**
     * @return QueueRepository|object
     */
    public function getQueueRepository(): QueueRepository
    {
        return GeneralUtility::makeInstance(QueueRepository::class);
    }

    /**
     * @return SentMessageRepository|object
     */
    public function getSentMessageRepository(): SentMessageRepository
    {
        return GeneralUtility::makeInstance(SentMessageRepository::class);
    }

}
