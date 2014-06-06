<?php
namespace Vanilla\Messenger\Utility;
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
 * A class dealing with configuration.
 *
 * @deprecated update implementation to make use of TYPO3 API for extension configuration.
 */
class Configuration implements \TYPO3\CMS\Core\SingletonInterface {

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
	 * @return \Vanilla\Messenger\Utility\Configuration
	 */
	static public function getInstance() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Vanilla\Messenger\Utility\Configuration');
	}

	/**
	 * Default settings in case it is not set in the Extension Manager.
	 *
	 * @var array
	 */
	protected $defaultSettings = array(
		'senderName' => 'John Doe',
		'senderEmail' => 'john@doe.com',
		'markerReplacedInLayout' => 'template',
		'listOfContextsSendingEmails' => 'Production',
		'rootPageUid' => '',
	);

	/**
	 * Constructor
	 *
	 * @return \Vanilla\Messenger\Utility\Configuration
	 */
	public function __construct() {
		$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extensionKey]);

		// Make sure settings can be merged!
		if (! is_array($settings)) {
			$settings = array();
		}
		$this->settings = \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge($this->defaultSettings, $settings);
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
