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
 * A class dealing with context.
 */
class Context implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var int
	 */
	protected $language = NULL;

	/**
	 * @var array
	 */
	protected $sendingEmailContexts;

	/**
	 * Returns a class instance
	 *
	 * @return \Vanilla\Messenger\Utility\Context
	 */
	static public function getInstance() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Vanilla\Messenger\Utility\Context');
	}

	/**
	 * Constructor
	 *
	 * @return \Vanilla\Messenger\Utility\Context
	 */
	public function __construct() {
		$settings = \Vanilla\Messenger\Utility\Configuration::getInstance()->getSettings();
		$this->name = empty($settings['context']) ? 'Development' : $settings['context'];
		$this->sendingEmailContexts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $settings['listOfContextsSendingEmails']);
	}

	/**
	 * @return int
	 */
	public function getLanguage() {
		if ($this->language === NULL) {
			$this->language = $GLOBALS['TSFE']->sys_language_content;
		}
		return intval($this->language);
	}

	/**
	 * @param int $language
	 */
	public function setLanguage($language) {
		$this->language = $language;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Tell whether the context is a debug one
	 *
	 * @return boolean
	 */
	public function isContextSendingEmails() {
		return in_array($this->getName(), $this->sendingEmailContexts);
	}

	/**
	 * Tell whether the context is not sending email
	 *
	 * @return boolean
	 */
	public function isContextNotSendingEmails() {
		return ! $this->isContextSendingEmails();
	}

	/**
	 * @return array
	 */
	public function getSendingEmailContexts() {
		return $this->sendingEmailContexts;
	}

	/**
	 * @param array $sendingEmailContexts
	 */
	public function setSendingEmailContexts($sendingEmailContexts) {
		$this->sendingEmailContexts = $sendingEmailContexts;
	}

	/**
	 * @return bool
	 */
	public function isProduction() {
		return $this->name === 'Production';
	}

	/**
	 * @return bool
	 */
	public function isNotProduction() {
		return $this->name !== 'Production';
	}

	/**
	 * @return bool
	 */
	public function isDevelopment() {
		return $this->name === 'Development';
	}

	/**
	 * @return bool
	 */
	public function isNotDevelopment() {
		return $this->name !== 'Development';
	}

}
