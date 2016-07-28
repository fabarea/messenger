<?php
namespace Fab\Messenger\Domain\Model;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\ContentRenderer\BackendRenderer;
use Fab\Messenger\ContentRenderer\FrontendRenderer;
use Fab\Messenger\Redirect\RedirectService;
use Fab\Messenger\Validator\EmailValidator;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Messenger\Exception\MissingFileException;
use Fab\Messenger\Exception\RecordNotFoundException;
use Fab\Messenger\Exception\WrongPluginConfigurationException;
use Fab\Messenger\Html2Text\TemplateEngine;
use Fab\Messenger\Service\MessageStorage;
use Fab\Messenger\Service\LoggerService;
use Fab\Messenger\Service\Html2Text;
use \Michelf\Markdown;

/**
 * Message representation
 */
class Message
{

    const SUBJECT = 'subject';
    const BODY = 'body';

    /**
     * @var int
     */
    protected $uid;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $sender = [];

    /**
     * The "to" addresses
     *
     * @var array
     */
    protected $to = [];

    /**
     * The "cc" addresses
     *
     * @var array
     */
    protected $cc = [];

    /**
     * The "bcc" addresses
     *
     * @var array
     */
    protected $bcc = [];

    /**
     * Addresses for reply-to
     *
     * @var array
     */
    protected $replyTo = [];

    /**
     * A set of markers.
     *
     * @var array
     */
    protected $markers = [];

    /**
     * @var \Fab\Messenger\Domain\Model\MessageLayout
     */
    protected $messageLayout;

    /**
     * @var string
     */
    protected $mailingName;

    /**
     * @var int
     */
    protected $scheduleDistributionTime = 0;

    /**
     * @var array
     */
    protected $attachments = [];

    /**
     * @var \Fab\Messenger\Domain\Repository\MessageTemplateRepository
     * @inject
     */
    protected $messageTemplateRepository;

    /**
     * @var \Fab\Messenger\Domain\Repository\MessageLayoutRepository
     * @inject
     */
    protected $messageLayoutRepository;

    /**
     * @var \Fab\Messenger\Domain\Repository\SentMessageRepository
     * @inject
     */
    protected $sentMessageRepository;

    /**
     * @var \Fab\Messenger\Domain\Repository\QueueRepository
     * @inject
     */
    protected $queueRepository;

    /**
     * @var \Fab\Messenger\Domain\Model\MessageTemplate
     */
    protected $messageTemplate;

    /**
     * @var MailMessage
     */
    protected $mailMessage;

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $body = '';

    /**
     * @var string
     */
    protected $processedSubject = '';

    /**
     * @var string
     */
    protected $processedBody = '';

    /**
     * @var bool
     */
    protected $parseToMarkdown = false;

    /**
     * Prepares the emails and queue it.
     *
     * @return void
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     */
    public function enqueue()
    {
        $this->prepareMessage();
        $this->queueRepository->add($this->toArray());
    }

    /**
     * Prepares the emails and send it.
     *
     * @throws WrongPluginConfigurationException
     * @return boolean whether or not the email was sent successfully
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function send()
    {

        $this->prepareMessage();

        $this->getMailMessage()->send();
        $isSent = $this->getMailMessage()->isSent();

        if ($isSent) {
            $this->sentMessageRepository->add($this->toArray());

            // Store body of the message for possible later use.
            if ($this->messageTemplate) {
                MessageStorage::getInstance()->set($this->messageTemplate->getUid(), $message['body']);
            }
        } else {
            $message = 'No Email sent, something went wrong. Check Swift Mail configuration';
            LoggerService::getLogger($this)->error($message);
            throw new WrongPluginConfigurationException($message, 1350124220);
        }

        return $isSent;
    }

    /**
     * Prepares the emails by fetching an email template and formats its body.
     *
     * @return boolean whether or not the email was sent successfully
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function prepareMessage()
    {
        if (!$this->to) {
            throw new \RuntimeException('Messenger: no recipient was defined', 1354536585);
        }

        $message = $this->getMailMessage()
            ->setTo($this->getTo())
            ->setCc($this->getCc())
            ->setBcc($this->getBcc())
            ->setFrom($this->getSender())
            ->setReplyTo($this->getReplyTo())
            ->setSubject($this->getProcessedSubject());

        // Attach plain text version if HTML tags are found in body
        if ($this->hasHtml($this->getProcessedBody())) {
            $message->setBody($this->getProcessedBody(), 'text/html');
            $text = Html2Text::getInstance()->convert($this->getProcessedBody());
            $this->getMailMessage()->addPart($text, 'text/plain');
        } else {
            $message->setBody($this->getProcessedBody(), 'text/plain');
        }

        // Handle attachment
        foreach ($this->attachments as $attachment) {
            $this->getMailMessage()->attach($attachment);
        }
    }

    /**
     * Retrieves the message template object
     *
     * @return \Fab\Messenger\Domain\Model\MessageTemplate
     */
    public function getMessageTemplate()
    {
        return $this->messageTemplate;
    }

    /**
     * Check whether a string contains HTML tags
     *
     * @see http://preprocess.me/how-to-check-if-a-string-contains-html-tags-in-php
     * @param string $content the content to be analyzed
     * @return boolean
     */
    public function hasHtml($content)
    {
        $result = FALSE;
        //we compare the length of the string with html tags and without html tags
        if (strlen($content) !== strlen(strip_tags($content))) {
            $result = TRUE;
        }
        return $result;
    }

    /**
     * Attach a file to the message.
     *
     * @throws MissingFileException
     * @param string $attachment an absolute path to a file
     * @return Message
     */
    public function addAttachment($attachment)
    {

        // Convert $file to absolute path.
        if ($attachment instanceof File) {
            $attachment = $attachment->getForLocalProcessing(FALSE);
        }

        // Makes sure the file exist
        if (is_file($attachment)) {
            $parts = explode('/', $attachment);
            $fileName = array_pop($parts);
            $this->attachments[] = \Swift_Attachment::fromPath($attachment)->setFilename($fileName);
        } else {
            $message = sprintf('File not found "%s"', $attachment);
            throw new MissingFileException($message, 1389779394);
        }
        return $this;
    }

    /**
     * Set multiple markers at once.
     *
     * @param array $values
     * @return Message
     */
    public function setMarkers($values)
    {
        foreach ($values as $markerName => $value) {
            $this->addMarker($markerName, $value);
        }
        return $this;
    }

    /**
     * Add a new marker and its value.
     *
     * @param string $markerName
     * @param mixed $value
     * @return Message
     */
    public function addMarker($markerName, $value)
    {
        $this->markers[$markerName] = $value;
        return $this;
    }

    /**
     * Set Markers
     *
     * @param mixed $values
     * @return Message
     * @deprecated
     */
    public function assignMultiple(array $values)
    {
        return $this->setMarkers($values);
    }

    /**
     * Add a new maker.
     *
     * @param string $markerName
     * @param mixed $value
     * @return Message
     */
    public function assign($markerName, $value)
    {
        return $this->addMarker($markerName, $value);
    }

    /**
     * Return "to" addresses.
     * Special case: override "to" if a redirection has been set for a Context.
     *
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }


    /**
     * Set "to" addresses. Should be an array('email' => 'name').
     *
     * @param mixed $addresses
     * @return Message
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \InvalidArgumentException
     */
    public function setTo($addresses)
    {
        $this->getEmailValidator()->validate($addresses);
        $this->to = $addresses;
        return $this;
    }

    /**
     * Return "cc" addresses.
     * Special case: there is no "cc" if a redirection has been set for a Context.
     *
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set "cc" addresses. Should be an array('email' => 'name').
     *
     * @param mixed $addresses
     * @return Message
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \InvalidArgumentException
     */
    public function setCc($addresses)
    {
        $this->getEmailValidator()->validate($addresses);
        $this->cc = $addresses;
        return $this;
    }

    /**
     * Return "bcc" addresses.
     * Special case: there is no "bcc" if a redirection has been set for a Context.
     *
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Set "cc" addresses. Should be an array('email' => 'name').
     *
     * @param mixed $addresses
     * @return Message
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \InvalidArgumentException
     */
    public function setBcc($addresses)
    {
        $this->getEmailValidator()->validate($addresses);
        $this->bcc = $addresses;
        return $this;
    }

    /**
     * @return array
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * Set "reply-to" addresses. Should be an array('email' => 'name').
     *
     * @param mixed $addresses
     * @return Message
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \InvalidArgumentException
     */
    public function setReplyTo($addresses)
    {
        $this->getEmailValidator()->validate($addresses);
        $this->replyTo = $addresses;
        return $this;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     */
    public function getSender()
    {

        // Compute sender from global configuration.
        if (!$this->sender) {
            if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'])) {
                throw new \RuntimeException('I could not find a sender email address. Missing value for "defaultMailFromAddress"', 1402032685);
            }

            $email = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
            if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'])) {
                $name = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
            } else {
                $name = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
            }

            $this->sender = array($email => $name);
            $this->getEmailValidator()->validate($this->sender);
        }

        return $this->sender;
    }

    /**
     * Re-set default sender
     *
     * @param array $sender
     * @return Message
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \InvalidArgumentException
     */
    public function setSender(array $sender)
    {
        $this->getEmailValidator()->validate($sender);
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getProcessedSubject()
    {
        if ($this->processedSubject === '') {

            $processedSubject = $this->subject;
            if ($this->messageTemplate) {
                $processedSubject = $this->messageTemplate->getSubject();
            }
            // Possible markers substitution.
            if ($this->markers) {
                $processedSubject = $this->getContentRenderer()->render($processedSubject, $this->markers);
            }
            $this->processedSubject = $processedSubject;
        }

        return $this->processedSubject;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getProcessedBody()
    {
        if ($this->processedBody === '') {

            $processedBody = $this->body;

            if ($this->messageTemplate) {
                $processedBody = $this->messageTemplate->getBody();
            }

            // Possible wrap body in Layout content.
            if ($this->messageLayout) {
                $processedBody = str_replace('{BODY}', $processedBody, $this->messageLayout->getContent());
            }

            // Parse Markdown only if necessary.
            if ($this->parseToMarkdown
                || ($this->messageTemplate && $this->messageTemplate->getTemplateEngine() === TemplateEngine::FLUID_AND_MARKDOWN)) {
                $processedBody = Markdown::defaultTransform($processedBody);
            }

            // Possible markers substitution.
            if ($this->markers) {
                $processedBody = $this->getContentRenderer()->render($processedBody, $this->markers);
            }


            $this->processedBody = $processedBody;
        }

        return $this->processedBody;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return \Fab\Messenger\Domain\Model\MessageLayout
     */
    public function getMessageLayout()
    {
        return $this->messageLayout;
    }

    /**
     * parameter $messageLayout can be:
     *      + \Fab\Messenger\Domain\Model\MessageLayout $messageLayout
     *      + int $messageLayout which corresponds to an uid
     *      + string $messageLayout which corresponds to a value for property "identifier".
     *
     * @throws RecordNotFoundException
     * @param mixed $messageLayout
     * @return Message
     */
    public function setMessageLayout($messageLayout)
    {
        if ($messageLayout instanceof MessageLayout) {
            $this->messageLayout = $messageLayout;
        } else {

            // try to convert message layout to a possible uid.
            if ((int)$messageLayout > 0) {
                $messageLayout = (int)$messageLayout;
            }
            $methodName = is_int($messageLayout) ? 'findByUid' : 'findByQualifier';
            $this->messageLayout = $this->messageLayoutRepository->$methodName($messageLayout);

            if ($this->messageLayout === null) {
                $message = sprintf('I could not find message layout "%s"', $messageLayout);
                throw new RecordNotFoundException($message, 1389769449);
            }
        }

        return $this;
    }

    /**
     * parameter $messageTemplate can be:
     *      + \Fab\Messenger\Domain\Model\MessageTemplate $messageTemplate
     *      + int $messageTemplate which corresponds to an uid
     *      + string $messageTemplate which corresponds to a value for property "identifier".
     *
     * @throws RecordNotFoundException
     * @param mixed $messageTemplate
     * @return Message
     */
    public function setMessageTemplate($messageTemplate)
    {

        if ($messageTemplate instanceof MessageTemplate) {
            $this->messageTemplate = $messageTemplate;
        } else {

            // try to convert message template to a possible uid.
            if ((int)$messageTemplate > 0) {
                $messageTemplate = (int)$messageTemplate;
            }
            $methodName = is_int($messageTemplate) ? 'findByUid' : 'findByQualifier';

            /** @var \Fab\Messenger\Domain\Model\MessageTemplate $messageTemplate */
            $messageTemplate = $this->messageTemplateRepository->$methodName($messageTemplate);

            if ($messageTemplate === null) {
                $message = sprintf('I could not find message template "%s"', $messageTemplate);
                throw new RecordNotFoundException($message, 1350124207);
            }

            $this->messageTemplate = $messageTemplate;
        }

        return $this;
    }

    /**
     * Tell whether the message has been prepared.
     *
     * @return boolean
     */
    protected function isMessagePrepared()
    {
        return !empty($this->mailMessage);
    }

    /**
     * Convert this object to an array.
     *
     * @return array
     * @throws \Exception
     */
    public function toArray()
    {

        if (!$this->isMessagePrepared()) {
            $this->prepareMessage();
        }

        $values = array(
            'sender' => $this->formatAddresses($this->getSender()),
            'to' => $this->formatAddresses($this->getTo()),
            'cc' => $this->formatAddresses($this->getCc()),
            'bcc' => $this->formatAddresses($this->getBcc()),
            'recipient' => $this->formatAddresses($this->getTo()),
            'recipient_cc' => $this->formatAddresses($this->getCc()),
            'recipient_bcc' => $this->formatAddresses($this->getBcc()),
            'reply_to' => $this->formatAddresses($this->getReplyTo()),
            'subject' => $this->getMailMessage()->getSubject(),
            'body' => $this->getMailMessage()->getBody(),
            'attachment' => count($this->attachments),
            'context' => (string)GeneralUtility::getApplicationContext(),
            'was_opened' => 0,
            'message_template' => is_object($this->messageTemplate) ? $this->messageTemplate->getUid() : 0,
            'message_layout' => is_object($this->messageLayout) ? $this->messageLayout->getUid() : 0,
            'scheduled_distribution_time' => $this->scheduleDistributionTime,
            'mailing_name' => $this->mailingName,
            'redirect_email' => $this->getRedirectService()->getRedirectionList(),
            'ip' => GeneralUtility::getIndpEnv('REMOTE_ADDR'),
            'mail_message' => $this->getMailMessage()
        );

        return $values;
    }

    /**
     * @param $parseToMarkdown
     * @return $this
     */
    public function parseToMarkdown($parseToMarkdown)
    {
        $this->parseToMarkdown = (bool)$parseToMarkdown;
        return $this;
    }

    /**
     * Format an array of addresses
     *
     * @param array $addresses
     * @return string
     */
    protected function formatAddresses(array $addresses)
    {
        $formattedAddresses = [];
        foreach ($addresses as $email => $name) {
            $formattedAddresses[] = sprintf('%s <%s>', $name, $email);
        }
        return implode(', ', $formattedAddresses);

    }

    /**
     * @return MailMessage
     */
    public function getMailMessage()
    {
        if ($this->mailMessage === null) {
            $this->mailMessage = $this->objectManager->get(MailMessage::class);
        }
        return $this->mailMessage;
    }

    /**
     * @return string
     */
    public function getMailingName()
    {
        return $this->mailingName;
    }

    /**
     * @param string $mailingName
     * @return $this
     */
    public function setMailingName($mailingName)
    {
        $this->mailingName = $mailingName;
        return $this;
    }

    /**
     * @return int
     */
    public function getScheduleDistributionTime()
    {
        return $this->scheduleDistributionTime;
    }

    /**
     * @param int $scheduleDistributionTime
     * @return $this
     */
    public function setScheduleDistributionTime($scheduleDistributionTime)
    {
        $this->scheduleDistributionTime = $scheduleDistributionTime;
        return $this;
    }

    /**
     * @return EmailValidator
     * @throws \InvalidArgumentException
     */
    public function getEmailValidator()
    {
        return GeneralUtility::makeInstance(EmailValidator::class);
    }

    /**
     * @return \Fab\Messenger\ContentRenderer\ContentRendererInterface
     * @throws \InvalidArgumentException
     */
    protected function getContentRenderer()
    {
        if ($this->isFrontendMode()) {
            /** @var FrontendRenderer $contentRenderer */
            $contentRenderer = GeneralUtility::makeInstance(FrontendRenderer::class, $this->messageTemplate);
        } else {
            /** @var BackendRenderer $contentRenderer */
            $contentRenderer = GeneralUtility::makeInstance(BackendRenderer::class);
        }
        return $contentRenderer;
    }

    /**
     * Returns whether the current mode is Frontend
     *
     * @return bool
     */
    protected function isFrontendMode()
    {
        return TYPO3_MODE === 'FE';
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
