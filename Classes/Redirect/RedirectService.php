<?php
namespace Fab\Messenger\Redirect;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
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
     * @throws \InvalidArgumentException
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     */
    public function getRedirections()
    {
        $recipientList = $this->getRedirectionList();
        return $this->transformEmailListToArray($recipientList);
    }

    /**
     * @param string $recipientList
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     */
    public function transformEmailListToArray($recipientList)
    {
        $recipients = [];
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
     * Get possible redirect recipients.
     *
     * @return array
     */
    public function getRedirectionList()
    {
        // Fetch email from PHP configuration array at first.
        $applicationContext = (string)GeneralUtility::getApplicationContext()->getParent();
        if (!$applicationContext) {
            $applicationContext = (string)GeneralUtility::getApplicationContext();
        }

        $key = strtolower($applicationContext) . '_redirect_to';
        if (isset($GLOBALS['TYPO3_CONF_VARS']['MAIL'][$key])) {
            $recipientList = $GLOBALS['TYPO3_CONF_VARS']['MAIL'][$key];
        } else {
            $recipientList = ConfigurationUtility::getInstance()->get($key);
        }
        return $recipientList;
    }

    /**
     * @return EmailValidator
     * @throws \InvalidArgumentException
     */
    public function getEmailValidator()
    {
        return GeneralUtility::makeInstance(EmailValidator::class);
    }

}
