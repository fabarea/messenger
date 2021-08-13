<?php
namespace Fab\Messenger\Redirect;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Validator\EmailValidator;
use TYPO3\CMS\Core\Core\Environment;
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
     */
    public function getRedirections(): array
    {
        $recipients = [];

        $recipientList = $this->getRedirectionList();
        if ($recipientList !== '') {
            $emails = GeneralUtility::trimExplode(',', $recipientList);

            foreach ($emails as $email) {
                $recipients[$email] = $email;
            }

            $this->getEmailValidator()->validate($recipients);
        }
        return $recipients;
    }

    /**
     * Get possible redirect recipients.
     *
     * @return string
     */
    public function getRedirectionList(): string
    {
        // Fetch email from PHP configuration array at first.
        $applicationContext = (string)Environment::getContext()->getParent();
        if (!$applicationContext) {
            $applicationContext = (string)Environment::getContext();
        }

        $key = strtolower($applicationContext) . '_redirect_to';
        if (isset($GLOBALS['TYPO3_CONF_VARS']['MAIL'][$key])) {
            $recipientList = (string)$GLOBALS['TYPO3_CONF_VARS']['MAIL'][$key];
        } else {
            $recipientList = (string)ConfigurationUtility::getInstance()->get($key);
        }
        return trim($recipientList);
    }

    /**
     * @return EmailValidator|object
     */
    public function getEmailValidator(): EmailValidator
    {
        return GeneralUtility::makeInstance(EmailValidator::class);
    }

}
