<?php
namespace TYPO3\CMS\Messenger\Utility;
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
	 * @return \TYPO3\CMS\Messenger\Utility\Configuration
	 */
	static public function getInstance() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Messenger\Utility\Configuration');
	}

	/**
	 * Default settings in case it is not set in the Extension Manager.
	 *
	 * @var array
	 */
	protected $defaultSettings = array(
		'tableStructure' => 'TYPO3\CMS\Messenger\ListManager\DemoListManager',
		'tableStructureFallBack' => 'TYPO3\CMS\Messenger\ListManager\DemoListManager',
		'developmentEmails' => 'john@doe.com, jane@doe.com',
		'context' => 'Development',
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
	 * @return \TYPO3\CMS\Messenger\Utility\Configuration
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

?>