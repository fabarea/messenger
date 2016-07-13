<?php
namespace Fab\Messenger\Domain\Model;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Sent Message representation
 */
class SentMessage extends AbstractEntity
{

    /**
     * @var int
     */
    protected $sentTime;

    /**
     * @return int $sentTime
     */
    public function getSentTime()
    {
        return $this->sentTime;
    }

    /**
     * @param int $sentTime
     * @return void
     */
    public function setSentTime($sentTime)
    {
        $this->sentTime = $sentTime;
    }

}
