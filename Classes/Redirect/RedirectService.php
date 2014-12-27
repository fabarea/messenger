<?php
namespace Vanilla\Messenger\Redirect;
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
 *  the Free Software Foundation; either version 3 of the License, or
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
use Vanilla\Messenger\Utility\ConfigurationUtility;

/**
 * Class providing service for redirection of emails
 */
class RedirectService implements SingletonInterface {

	/**
	 * Get possible redirect recipients.
	 *
	 * @return array
	 */
	public function redirectionForCurrentContext() {

		// Fetch email from PHP configuration array at first.
		$applicationContext = strtolower((string)GeneralUtility::getApplicationContext());
		$key = $applicationContext . '_redirect_to';
		if (isset($GLOBALS['TYPO3_CONF_VARS']['MAIL'][$key])) {
			$recipientList = $GLOBALS['TYPO3_CONF_VARS']['MAIL'][$key];
		} else {
			$recipientList = ConfigurationUtility::getInstance()->get($key);
		}

		$recipients = array();
		if (strlen(trim($recipientList))> 0) {
			$emails = GeneralUtility::trimExplode(',', $recipientList);

			foreach ($emails as $email) {
				$recipients[$email] = $email;
			}

			$this->getEmailValidator()->validate($recipients);
		}

		return $recipients;
	}

	/**
	 * @return \Vanilla\Messenger\Validator\EmailValidator
	 */
	public function getEmailValidator() {
		return GeneralUtility::makeInstance('Vanilla\Messenger\Validator\EmailValidator');
	}

}
