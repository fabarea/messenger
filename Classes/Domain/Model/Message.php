<?php

namespace Fab\Messenger\Domain\Model;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\ContentRenderer\ContentRendererInterface;
use Fab\Messenger\ContentRenderer\FrontendRenderer;
use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Redirect\RedirectService;
use Fab\Messenger\Validator\EmailValidator;
use RuntimeException;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Core\Environment;
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
use Michelf\Markdown;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Message representation
 */
class Message
{

    final const SUBJECT = 'subject';
    final const BODY = 'body';

    /**
     * @var int
     */
    protected $uid;

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
     * Possible email redirect
     *
     * @var array
     */
    protected $redirectEmailFrom = [];

    /**
     * A set of markers.
     *
     * @var array
     */
    protected $markers = [];

    /**
     * @var MessageLayout
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
     * @var MessageTemplateRepository
     */
    protected $messageTemplateRepository;

    /**
     * @var MessageLayoutRepository
     */
    protected $messageLayoutRepository;

    /**
     * @var SentMessageRepository
     */
    protected $sentMessageRepository;

    /**
     * @var MessageTemplate
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
     * @var string
     */
    protected $uuid = '';

    public function __construct()
    {
        // todo legacy, migrate me!
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->messageTemplateRepository = $objectManager->get(MessageTemplateRepository::class);
        $this->messageLayoutRepository = $objectManager->get(MessageLayoutRepository::class);
        $this->sentMessageRepository = $objectManager->get(SentMessageRepository::class);
    }

    /**
     * Prepares the emails and queue it.
     */
    public function enqueue(): void
    {
        $this->prepareMessage();
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        $queueRepository->add($this->toArray());
    }

    /**
     * Prepares the emails and send it.
     *
     * @return boolean whether or not the email was sent successfully
     */
    public function send(): bool
    {
        $this->prepareMessage();

        $this->getMailMessage()->send();
        $isSent = $this->getMailMessage()->isSent();

        if ($isSent) {
            $this->sentMessageRepository->add($this->toArray());

            // Store body of the message for possible later use.
            if ($this->messageTemplate) {
                MessageStorage::getInstance()->set($this->messageTemplate->getUid(), $this->getMailMessage()->getBody());
            }
        } else {
            $message = 'No Email sent, something went wrong. Check Swift Mail configuration';
            LoggerService::getLogger($this)->error($message);
            throw new WrongPluginConfigurationException($message, 1_350_124_220);
        }

        return $isSent;
    }

    /**
     * Prepares the emails by fetching an email template and formats its body.
     */
    protected function prepareMessage(): void
    {
        if (!$this->to) {
            throw new RuntimeException('Messenger: no recipient was defined', 1_354_536_585);
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
            $message->setBody()->html($this->getProcessedBody());
            $text = Html2Text::getInstance()->convert($this->getProcessedBody());
            $message->setBody()->text($text);
        } else {
            $message->setBody()->text($this->getProcessedBody());
        }

        // Handle attachment
        foreach ($this->attachments as $attachment) {
            $this->getMailMessage()->attachFromPath($attachment);
        }

        // Handle email "redirection"
        $redirectTo = $this->getRedirectService()->getRedirections();

        // Means we want to redirect email.
        if ($redirectTo) {
            $this->redirectEmailFrom = $this->getMailMessage()->getTo();

            $this->getMailMessage()
                ->setBody()->html($this->getDebugInfoBody())
                ->setTo($redirectTo)
                ->setCc([])// reset cc which was written as debug in the body message previously.
                ->setBcc([])// same remark as bcc.
                ->setSubject($this->getDebugInfoSubject());
        }
    }

    protected function getDebugInfoSubject(): string
    {
        $applicationContext = (string)Environment::getContext();
        return strtoupper($applicationContext) . ' CONTEXT! ' . $this->getSubject();
    }

    /**
     * Get a body message when email is not in production.
     */
    protected function getDebugInfoBody(): string
    {
        $to = $this->getMailMessage()->getTo();
        $cc = $this->getMailMessage()->getCc();
        $bcc = $this->getMailMessage()->getBcc();

        return sprintf(
            "%s CONTEXT: this message is for testing purposes. In Production, it will be sent as follows. \nto: %s\n%s%s\n%s",
            strtoupper((string)Environment::getContext()),
            implode(',', array_keys($to)),
            empty($cc) ? '' : sprintf('cc: %s <br/>', implode(',', array_keys($cc))),
            empty($bcc) ? '' : sprintf('bcc: %s <br/>', implode(',', array_keys($bcc))),
            $this->getMailMessage()->getBody()
        );
    }

    /**
     * Retrieves the message template object
     */
    public function getMessageTemplate(): MessageTemplate
    {
        return $this->messageTemplate;
    }

    /**
     * Check whether a string contains HTML tags
     *
     * @see http://preprocess.me/how-to-check-if-a-string-contains-html-tags-in-php
     * @param string $content the content to be analyzed
     */
    public function hasHtml($content): bool
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
     * @param string $attachment an absolute path to a file
     */
    public function addAttachment($attachment): Message
    {

        // Convert $file to absolute path.
        if ($attachment instanceof File) {
            $attachment = $attachment->getForLocalProcessing(FALSE);
        }

        // Makes sure the file exist
        if (is_file($attachment)) {
            #$parts = explode('/', $attachment);
            #$fileName = array_pop($parts);
            $this->attachments[] = $attachment;
        } else {
            $message = sprintf('File not found "%s"', $attachment);
            throw new MissingFileException($message, 1_389_779_394);
        }
        return $this;
    }

    /**
     * Set multiple markers at once.
     *
     * @param array $values
     */
    public function setMarkers($values): Message
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
     */
    public function addMarker($markerName, $value): Message
    {
        $this->markers[$markerName] = $value;
        return $this;
    }

    /**
     * Set Markers
     *
     * @param mixed $values
     */
    public function assignMultiple(array $values): Message
    {
        foreach ($values as $markerName => $value) {
            $this->addMarker($markerName, $value);
        }
        return $this;
    }

    /**
     * Add a new maker.
     *
     * @param string $markerName
     * @param mixed $value
     */
    public function assign($markerName, $value): Message
    {
        return $this->addMarker($markerName, $value);
    }

    /**
     * Return "to" addresses.
     * Special case: override "to" if a redirection has been set for a Context.
     */
    public function getTo(): array
    {
        return $this->to;
    }


    /**
     * Set "to" addresses. Should be an array('email' => 'name').
     *
     * @param mixed $addresses
     */
    public function setTo($addresses): Message
    {
        $this->getEmailValidator()->validate($addresses);
        $this->to = $addresses;
        return $this;
    }

    /**
     * Return "cc" addresses.
     * Special case: there is no "cc" if a redirection has been set for a Context.
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * Set "cc" addresses. Should be an array('email' => 'name').
     *
     * @param mixed $addresses
     */
    public function setCc($addresses): Message
    {
        $this->getEmailValidator()->validate($addresses);
        $this->cc = $addresses;
        return $this;
    }

    /**
     * Return "bcc" addresses.
     * Special case: there is no "bcc" if a redirection has been set for a Context.
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * Set "cc" addresses. Should be an array('email' => 'name').
     *
     * @param mixed $addresses
     */
    public function setBcc($addresses): Message
    {
        $this->getEmailValidator()->validate($addresses);
        $this->bcc = $addresses;
        return $this;
    }

    public function getReplyTo(): array
    {
        return $this->replyTo;
    }

    /**
     * Set "reply-to" addresses. Should be an array('email' => 'name').
     *
     * @param mixed $addresses
     */
    public function setReplyTo($addresses): Message
    {
        $this->getEmailValidator()->validate($addresses);
        $this->replyTo = $addresses;
        return $this;
    }

    public function getSender(): array
    {
        // Compute sender from global configuration.
        if (!$this->sender) {
            if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'])) {
                throw new RuntimeException('I could not find a sender email address. Missing value for "defaultMailFromAddress"', 1_402_032_685);
            }

            $email = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
            if (!empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'])) {
                $name = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
            } else {
                $name = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
            }

            $this->sender = [$email => $name];
            $this->getEmailValidator()->validate($this->sender);
        }

        return $this->sender;
    }

    /**
     * Re-set default sender
     */
    public function setSender(array $sender): Message
    {
        $this->getEmailValidator()->validate($sender);
        $this->sender = $sender;
        return $this;
    }

    protected function getProcessedSubject(): string
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
     * @return $this
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    protected function getProcessedBody(): string
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
                || ($this->messageTemplate && $this->messageTemplate->getTemplateEngine() === TemplateEngine::FLUID_AND_MARKDOWN)
            ) {
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

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getMessageLayout(): MessageLayout
    {
        return $this->messageLayout;
    }

    /**
     * parameter $messageLayout can be:
     *      + \Fab\Messenger\Domain\Model\MessageLayout $messageLayout
     *      + int $messageLayout which corresponds to an uid
     *      + string $messageLayout which corresponds to a value for property "identifier".
     *
     * @param mixed $messageLayout
     */
    public function setMessageLayout($messageLayout): Message
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
                throw new RecordNotFoundException($message, 1_389_769_449);
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
     * @param mixed $messageTemplate
     */
    public function setMessageTemplate($messageTemplate): Message
    {
        if ($messageTemplate instanceof MessageTemplate) {
            $this->messageTemplate = $messageTemplate;
        } else {

            // try to convert message template to a possible uid.
            if ((int)$messageTemplate > 0) {
                $messageTemplate = (int)$messageTemplate;
            }
            $methodName = is_int($messageTemplate) ? 'findByUid' : 'findByQualifier';

            /** @var MessageTemplate $messageTemplate */
            $messageTemplate = $this->messageTemplateRepository->$methodName($messageTemplate);

            if ($messageTemplate === null) {
                $message = sprintf('I could not find message template "%s"', $messageTemplate);
                throw new RecordNotFoundException($message, 1_350_124_207);
            }

            $this->messageTemplate = $messageTemplate;
        }

        return $this;
    }

    /**
     * Tell whether the message has been prepared.
     */
    protected function isMessagePrepared(): bool
    {
        return !empty($this->mailMessage);
    }

    /**
     * Convert this object to an array.
     */
    public function toArray(): array
    {

        if (!$this->isMessagePrepared()) {
            $this->prepareMessage();
        }

        $mailMessage = $this->getMailMessage();
        $values = [
            'sender' => $this->formatAddresses($mailMessage->getFrom()),
            'to' => $this->formatAddresses($mailMessage->getTo()),
            'cc' => $this->formatAddresses($mailMessage->getCc()),
            'bcc' => $this->formatAddresses($mailMessage->getBcc()),
            'recipient' => $this->formatAddresses($mailMessage->getTo()),
            'recipient_cc' => $this->formatAddresses($mailMessage->getCc()),
            'recipient_bcc' => $this->formatAddresses($mailMessage->getBcc()),
            'reply_to' => $mailMessage->getReplyTo(),
            'subject' => $mailMessage->getSubject(),
            'body' => $mailMessage->getBody(),
            'attachment' => count($this->attachments),
            'context' => (string)Environment::getContext(),
            'was_opened' => 0,
            'message_template' => is_object($this->messageTemplate) ? $this->messageTemplate->getUid() : 0,
            'message_layout' => is_object($this->messageLayout) ? $this->messageLayout->getUid() : 0,
            'scheduled_distribution_time' => $this->scheduleDistributionTime,
            'mailing_name' => $this->mailingName ?: '',
            'redirect_email_from' => $this->formatAddresses($this->redirectEmailFrom),
            'ip' => GeneralUtility::getIndpEnv('REMOTE_ADDR') ?: '',
            'mail_message' => $mailMessage,
            'uuid' => $this->uuid,
        ];

        return $values;
    }

    /**
     * @param $parseToMarkdown
     * @return $this
     */
    public function parseToMarkdown($parseToMarkdown): self
    {
        $this->parseToMarkdown = (bool)$parseToMarkdown;
        return $this;
    }

    /**
     * Format an array of addresses
     */
    protected function formatAddresses(array $addresses): string
    {
        $formattedAddresses = [];
        /** @var Address $addresses */
        foreach ($addresses as $address) {
            $formattedAddresses[] = sprintf('%s <%s>', $address->getName(), $address->getAddress());
        }
        return implode(', ', $formattedAddresses);

    }

    public function getMailMessage(): MailMessage
    {
        if ($this->mailMessage === null) {
            $this->mailMessage = GeneralUtility::makeInstance(MailMessage::class);
        }
        return $this->mailMessage;
    }

    public function getMailingName(): string
    {
        return $this->mailingName;
    }

    /**
     * @param string $mailingName
     * @return $this
     */
    public function setMailingName($mailingName): self
    {
        $this->mailingName = $mailingName;
        return $this;
    }

    public function getScheduleDistributionTime(): int
    {
        return $this->scheduleDistributionTime;
    }

    /**
     * @param int $scheduleDistributionTime
     * @return $this
     */
    public function setScheduleDistributionTime($scheduleDistributionTime): self
    {
        $this->scheduleDistributionTime = $scheduleDistributionTime;
        return $this;
    }

    /**
     * @return EmailValidator
     */
    public function getEmailValidator(): EmailValidator
    {
        return GeneralUtility::makeInstance(EmailValidator::class);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return $this
     */
    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return ContentRendererInterface
     */
    protected function getContentRenderer(): ContentRendererInterface
    {
        return GeneralUtility::makeInstance(FrontendRenderer::class, $this->messageTemplate ?: null);
        #if ($this->isFrontendMode()) {
        #    /** @var FrontendRenderer $contentRenderer */
        #    $contentRenderer = GeneralUtility::makeInstance(FrontendRenderer::class, $this->messageTemplate);
        #} else {
        #    /** @var BackendRenderer $contentRenderer */
        #    $contentRenderer = GeneralUtility::makeInstance(BackendRenderer::class);
        #}
        #return $contentRenderer;
    }

    /**
     * @return RedirectService
     */
    public function getRedirectService(): RedirectService
    {
        return GeneralUtility::makeInstance(RedirectService::class);
    }

}
