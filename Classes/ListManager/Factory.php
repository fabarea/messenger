<?php
namespace Vanilla\Messenger\ListManager;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * A factory for list manager.
 */
class Factory {

	/**
	 * @var \Vanilla\Messenger\MessengerInterface\ListableInterface
	 */
	static protected $instance = NULL;

	/**
	 * Get an instance of a list manager interface
	 *
	 * @return \Vanilla\Messenger\MessengerInterface\ListableInterface
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			$className = \Vanilla\Messenger\Utility\BeUserPreference::get('messenger_list_manager');

			if (! class_exists($className)) {
				$className = \Vanilla\Messenger\Utility\Configuration::getInstance()->get('tableStructureFallBack');
			}

			self::$instance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($className);
		}
		return self::$instance;
	}

}
