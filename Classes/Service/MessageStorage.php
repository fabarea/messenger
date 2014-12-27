<?php
namespace Vanilla\Messenger\Service;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class dealing with MessageStorage.
 */
class MessageStorage implements SingletonInterface {

	/**
	 * @var string
	 */
	protected $namespace = 'Vanilla\Messenger\\';

	/**
	 * Returns a class instance
	 *
	 * @return \Vanilla\Messenger\Service\MessageStorage
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance('Vanilla\Messenger\Service\MessageStorage');
	}

	/**
	 * Get a stored value for this run time.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		$value = NULL;
		if ($this->isFrontendMode()) {
			$value = $this->getFrontendUser()->getKey('ses', $this->namespace . $key);
			$this->getFrontendUser()->setKey('ses', $this->namespace . $key, NULL); // unset variable
		}
		return $value;
	}

	/**
	 * Store a value for this run time.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	public function set($key, $value) {
		if ($this->isFrontendMode()) {
			$this->getFrontendUser()->setKey('ses', $this->namespace . $key, $value);
		}
		return $this;
	}

	/**
	 * Returns an instance of the current Frontend User.
	 *
	 * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
	 */
	protected function getFrontendUser() {
		return $GLOBALS['TSFE']->fe_user;
	}

	/**
	 * Returns whether the current mode is Frontend
	 *
	 * @return bool
	 */
	protected function isFrontendMode() {
		return TYPO3_MODE == 'FE';
	}
}
