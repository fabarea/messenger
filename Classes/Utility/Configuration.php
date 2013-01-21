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
class Tx_Messenger_Utility_Configuration implements t3lib_Singleton {

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var string
	 */
	protected $extensionKey = 'messenger';

	/**
	 * Returns a class instance
	 *
	 * @return Tx_Messenger_Utility_Configuration
	 */
	static public function getInstance() {
		return t3lib_div::makeInstance('Tx_Messenger_Utility_Configuration');
	}

	/**
	 * Default settings in case it is not set in the Extension Manager.
	 *
	 * @var array
	 */
	protected $defaultSettings = array(
		'tableStructure' => 'Tx_Messenger_ListManager_DemoListManager',
		'tableStructureFallBack' => 'Tx_Messenger_ListManager_DemoListManager',
		'developmentEmails' => 'john@doe.com, jane@doe.com',
		'context' => 'Development',
		'messageUid' => '',
		'senderName' => 'John Doe',
		'senderEmail' => 'john@doe.com',
		'markerReplacedInLayout' => 'template',
		'listOfContextsSendingEmails' => 'Production',
		'rootPageUid' => '',
		'sendMultipartedEmail' => TRUE,
		'enableBeModule' => TRUE,
	);

	/**
	 * Constructor
	 *
	 * @return Tx_Messenger_Utility_Configuration
	 */
	public function __construct() {
		$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extensionKey]);
		$this->settings = t3lib_div::array_merge($this->defaultSettings, $settings);
	}

	/**
	 * Returns a configuration key.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return isset($this->settings[$key]) ? $this->settings[$key] : '';
	}

	/**
	 * @return array
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * @param array $settings
	 */
	public function setSettings(array $settings = array()) {
		$this->settings = $settings;
	}
}

?>