<?php

namespace Fab\Messenger\Domain\Model;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Sent Message representation
 */
class SentMessage extends AbstractEntity
{
    protected int $sentTime;

    public function getSentTime(): int
    {
        return $this->sentTime;
    }

    /**
     * @param int $sentTime
     * @return void
     */
    public function setSentTime(int $sentTime): void
    {
        $this->sentTime = $sentTime;
    }
}
