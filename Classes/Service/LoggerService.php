<?php
namespace Fab\Messenger\Service;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use InvalidArgumentException;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class to return an appropriate logger.
 */
class LoggerService
{

    /**
     * @param object $object
     * @return Logger
     * @throws InvalidArgumentException
     */
    static public function getLogger($object)
    {

        /** @var $loggerManager LogManager */
        $loggerManager = GeneralUtility::makeInstance(LogManager::class);

        /** @var $logger Logger */
        return $loggerManager->getLogger($object::class);
    }

}
