<?php
namespace Fab\Messenger\Service;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class to return an appropriate logger.
 */
class LoggerService
{

    /**
     * @param object $object
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    static public function getLogger($object)
    {

        /** @var $loggerManager \TYPO3\CMS\Core\Log\LogManager */
        $loggerManager = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager');

        /** @var $logger \TYPO3\CMS\Core\Log\Logger */
        return $loggerManager->getLogger(get_class($object));
    }
}
