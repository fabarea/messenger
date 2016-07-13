<?php
namespace Fab\Messenger\Override\Core\Mail;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adapter for Swift_Mailer to be used by TYPO3 extensions
 */
class MailMessage extends \TYPO3\CMS\Core\Mail\MailMessage
{

    /**
     * Sends the message.
     *
     * @return integer the number of recipients who were accepted for delivery
     */
    public function send()
    {
        $redirectTo = $this->getRedirectService()->redirectionForCurrentContext();

        // Means we want to redirect email.
        if (!empty($redirectTo)) {
            $body = $this->addDebugInfoToBody($this->getBody());
            $this->setBody($body);
            $this->setTo($redirectTo);
            $this->setCc(array()); // reset cc which was written as debug in the body message previously.
            $this->setBcc(array()); // same remark as bcc.

            $subject = strtoupper((string)GeneralUtility::getApplicationContext()) . ' CONTEXT! ' . $this->getSubject();
            $this->setSubject($subject);
        }
        return parent::send();
    }


    /**
     * Get a body message when email is not in production.
     *
     * @param string $messageBody
     * @return string
     */
    protected function addDebugInfoToBody($messageBody)
    {
        $to = $this->getTo();
        $cc = $this->getCc();
        $bcc = $this->getBcc();

        $messageBody = sprintf("%s CONTEXT: this message is for testing purpose. In reality, it would be sent: <br />to: %s<br />%s%s<br />%s",
            strtoupper((string)GeneralUtility::getApplicationContext()),
            implode(',', array_keys($to)),
            empty($cc) ? '' : sprintf('cc: %s <br/>', implode(',', array_keys($cc))),
            empty($bbc) ? '' : sprintf('bcc: %s <br/>', implode(',', array_keys($bcc))),
            $messageBody
        );

        return $messageBody;
    }

    /**
     * @return \Fab\Messenger\Redirect\RedirectService
     */
    public function getRedirectService()
    {
        return GeneralUtility::makeInstance('\Fab\Messenger\Redirect\RedirectService');
    }

}
