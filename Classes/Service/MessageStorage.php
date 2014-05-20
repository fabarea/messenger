<?php
namespace Vanilla\Messenger\Service;
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
		$value = $this->getFrontendUser()->getKey('ses', $this->namespace . $key);
		$this->getFrontendUser()->setKey('ses', $this->namespace . $key, NULL); // unset variable
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
		$this->getFrontendUser()->setKey('ses', $this->namespace . $key, $value);
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

}
