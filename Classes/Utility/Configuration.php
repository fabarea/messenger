<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012
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
 * A class dealing with configuration.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  messenger
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_Utility_Configuration {

	/**
	 * @var array
	 */
	static protected $settings = array();

	/**
	 * @var array
	 */
	static protected $defaultSettings = array(
		'tableStructure' => 'Tx_Messenger_ListManager_DemoListManager',
		'developmentEmails' => 'john@doe.com, jane@doe.com',
		'context' => 'Development',
	);

	/**
	 * @var string
	 */
	static protected $extensionKey = 'messenger';

	/**
	 * Returns a configuration key
	 *
	 * @param string $key
	 * @return array
	 */
	static public function get($key) {

		if (empty(self::$settings)) {
			$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extensionKey]);
			self::$settings = t3lib_div::array_merge(self::$defaultSettings, $settings);
		}
		return isset(self::$settings[$key]) ? self::$settings[$key] : '';
	}

	/**
	 * @return array
	 */
	public static function getSettings() {
		return self::$settings;
	}

	/**
	 * @param array $settings
	 */
	public static function setSettings($settings) {
		self::$settings = $settings;
	}

}

?>