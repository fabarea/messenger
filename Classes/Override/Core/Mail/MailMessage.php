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

use Fab\Messenger\Redirect\RedirectService;
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
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \InvalidArgumentException
     */
    public function send()
    {

        $applicationContext = (string)GeneralUtility::getApplicationContext();
        $redirectTo = $this->getRedirectService()->getRedirections();

        // hack! Retrieve from the message subject the application context
        // Reason: in CLI, when processing the email queue, we loose the FE application context information.
        // We use the subject to pass info / state of the FE application context.
        $subject = $this->getSubject();
        $subjectParts = explode('###REDIRECT###', $subject);

        if (count($subjectParts) > 1) {
            list($redirection, $sanitizedSubject) = $subjectParts;
            $this->setSubject($sanitizedSubject); // reset clean-up subject.

            $redirectionParts = explode('---', $redirection);
            list($applicationContext, $email) = $redirectionParts;
            $redirectTo = $this->getRedirectService()->transformEmailListToArray($email);
        }

        // Means we want to redirect email.
        if ($redirectTo) {
            $body = $this->addDebugInfoToBody($this->getBody());
            $this->setBody($body);
            $this->setTo($redirectTo);
            $this->setCc([]); // reset cc which was written as debug in the body message previously.
            $this->setBcc([]); // same remark as bcc.

            $subject = strtoupper($applicationContext) . ' CONTEXT! ' . $this->getSubject();
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

        $messageBody = sprintf(
            "%s CONTEXT: this message is for testing purposes. In Production, it will be sent as follows. \nto: %s\n%s%s\n%s",
            strtoupper((string)GeneralUtility::getApplicationContext()),
            implode(',', array_keys($to)),
            empty($cc) ? '' : sprintf('cc: %s <br/>', implode(',', array_keys($cc))),
            empty($bbc) ? '' : sprintf('bcc: %s <br/>', implode(',', array_keys($bcc))),
            $messageBody
        );

        return $messageBody;
    }

    /**
     * @return RedirectService
     * @throws \InvalidArgumentException
     */
    public function getRedirectService()
    {
        return GeneralUtility::makeInstance(RedirectService::class);
    }

}
