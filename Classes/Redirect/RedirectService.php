<?php
namespace Fab\Messenger\Redirect;

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

use Fab\Messenger\Validator\EmailValidator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Messenger\Utility\ConfigurationUtility;

/**
 * Class providing service for redirection of emails
 */
class RedirectService implements SingletonInterface
{

    /**
     * Get possible redirect recipients.
     *
     * @return array
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     */
    public function redirectionForCurrentContext()
    {

        // Fetch email from PHP configuration array at first.
        $applicationContext = (string)GeneralUtility::getApplicationContext()->getParent();
        if (empty($applicationContext)) {
            $applicationContext = (string)GeneralUtility::getApplicationContext();
        }
        $applicationContext = strtolower($applicationContext);

        $key = $applicationContext . '_redirect_to';
        if (isset($GLOBALS['TYPO3_CONF_VARS']['MAIL'][$key])) {
            $recipientList = $GLOBALS['TYPO3_CONF_VARS']['MAIL'][$key];
        } else {
            $recipientList = ConfigurationUtility::getInstance()->get($key);
        }

        $recipients = array();
        if (strlen(trim($recipientList)) > 0) {
            $emails = GeneralUtility::trimExplode(',', $recipientList);

            foreach ($emails as $email) {
                $recipients[$email] = $email;
            }

            $this->getEmailValidator()->validate($recipients);
        }

        return $recipients;
    }

    /**
     * @return EmailValidator
     */
    public function getEmailValidator()
    {
        return GeneralUtility::makeInstance(EmailValidator::class);
    }

}
