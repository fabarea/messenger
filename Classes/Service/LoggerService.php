<?php
namespace TYPO3\CMS\Messenger\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class to return an appropriate logger.
 */
class LoggerService {

	/**
	 * @param object $object
	 * @return \TYPO3\CMS\Core\Log\Logger
	 */
	static public function getLogger($object) {

		/** @var $loggerManager \TYPO3\CMS\Core\Log\LogManager */
		$loggerManager = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager');

		/** @var $logger \TYPO3\CMS\Core\Log\Logger */
		return $loggerManager->getLogger(get_class($object));
	}
}

?>