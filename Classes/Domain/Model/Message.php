<?php
namespace Vanilla\Messenger\Domain\Model;
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
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use Vanilla\Messenger\Exception\MissingFileException;
use Vanilla\Messenger\Exception\RecordNotFoundException;
use Vanilla\Messenger\Exception\WrongPluginConfigurationException;
use Vanilla\Messenger\Service\MessageStorage;
use Vanilla\Messenger\Service\LoggerService;
use Vanilla\Messenger\Service\Html2Text;
use \Michelf\Markdown;

/**
 * Message representation
 * @todo remove language handling from the class which should be managed outside - or not?
 */
class Message {

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
	protected $sender = array();

	/**
	 * The "to" addresses
	 *
	 * @var array
	 */
	protected $to = array();

	/**
	 * The "cc" addresses
	 *
	 * @var array
	 */
	protected $cc = array();

	/**
	 * The "bcc" addresses
	 *
	 * @var array
	 */
	protected $bcc = array();

	/**
	 * Addresses for reply-to
	 *
	 * @var array
	 */
	protected $replyTo = array();

	/**
	 * A set of markers.
	 *
	 * @var array
	 */
	protected $markers = array();

	/**
	 * @var int
	 */
	protected $language;

	/**
	 * @var \Vanilla\Messenger\Domain\Model\MessageLayout
	 */
	protected $messageLayout;

	/**
	 * @var \Vanilla\Messenger\Domain\Model\Mailing
	 */
	protected $mailing;

	/**
	 * @var array
	 */
	protected $attachments = array();

	/**
	 * @var \Vanilla\Messenger\Domain\Repository\MessageTemplateRepository
	 * @inject
	 */
	protected $messageTemplateRepository;

	/**
	 * @var \Vanilla\Messenger\Domain\Repository\MessageLayoutRepository
	 * @inject
	 */
	protected $messageLayoutRepository;

	/**
	 * @var \Vanilla\Messenger\Domain\Repository\SentMessageRepository
	 * @inject
	 */
	protected $sentMessageRepository;

	/**
	 * @var \Vanilla\Messenger\Domain\Repository\QueueRepository
	 * @inject
	 */
	protected $queueRepository;

	/**
	 * @var \Vanilla\Messenger\Domain\Model\MessageTemplate
	 */
	protected $messageTemplate;

	/**
	 * @var \TYPO3\CMS\Core\Mail\MailMessage
	 */
	protected $mailMessage;

	/**
	 * Constructor
	 */
	public function __construct() {

		if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'])) {
			throw new \Exception('I could not find a sender email address. Missing value for "defaultMailFromAddress"', 1402032685);
		}

		if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'])) {
			throw new \Exception('I could not find a sender name. Missing value for "defaultMailFromName"', 1402032686);
		}

		$this->sender = array(
			$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] => $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);

		$this->getEmailValidator()->validate($this->sender);
	}

	/**
	 * Prepares the emails and queue it.
	 *
	 * @return void
	 */
	public function queue() {
		$this->prepareMessage();
		$this->queueRepository->add($this->toArray());
	}

	/**
	 * Prepares the emails and send it.
	 *
	 * @throws WrongPluginConfigurationException
	 * @return boolean whether or not the email was sent successfully
	 */
	public function send() {

		$this->prepareMessage();

		$this->getMailMessage()->send();
		$isSent = $this->getMailMessage()->isSent();

		if ($isSent) {
			$message = $this->toArray();
			$this->sentMessageRepository->add($message);

			// Store body of the message for possible later use.
			MessageStorage::getInstance()->set($this->messageTemplate->getUid(), $message['body']);
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
	 * @throws \RuntimeException
	 * @return boolean whether or not the email was sent successfully
	 */
	protected function prepareMessage() {

		if (empty($this->messageTemplate)) {
			throw new \RuntimeException('Messenger: message template was not defined', 1354536584);
		}

		if (empty($this->to)) {
			throw new \RuntimeException('Messenger: no recipient was defined', 1354536585);
		}

		// Substitute markers
		$subject = $this->getContentRenderer()->render($this->messageTemplate->getSubject(), $this->markers);
		$body = $this->getContentRenderer()->render($this->messageTemplate->getBody(), $this->markers);

		// Tamper data in case the Development or Testing context is on.
//		if (!GeneralUtility::getApplicationContext()->isProduction()) {
//			$body = $this->getBodyForApplicationContext($body);
//			$this->to = $this->getRecipientsForDevelopmentContext();
//			// empty "cc" and "bcc" for non-production context -> has been put as debug info in the body of the message.
//			$this->cc = array();
//			$this->bcc = array();
//		}

		// Parse Markdown only if necessary
		if ($this->messageTemplate->getTemplateEngine() == 'both') {
			$body = Markdown::defaultTransform($body);
		}

		$this->getMailMessage()->setTo($this->to)
			->setFrom($this->sender)
			->setSubject($subject)
			->setBody($body, 'text/html');

		// Add possible CC.
		if (!empty($this->cc)) {
			$this->getMailMessage()->setCc($this->cc);
		}

		// Add possible BCC.
		if (!empty($this->bcc)) {
			$this->getMailMessage()->setBcc($this->bcc);
		}

		// Add possible reply-to.
		if (!empty($this->replyTo)) {
			$this->getMailMessage()->setReplyTo($this->replyTo);
		}

		// Attach plain text version if HTML tags are found in body
		if ($this->hasHtml($body)) {
			$text = Html2Text::getInstance()->convert($body);
			$this->getMailMessage()->addPart($text, 'text/plain');
		}

		// Handle attachment
		foreach ($this->attachments as $attachment) {
			$this->getMailMessage()->attach($attachment);
		}
	}

	/**
	 * Get a body message when email is not in production.
	 *
	 * @param string $messageBody
	 * @return string
	 */
	protected function getBodyForApplicationContext($messageBody) {
		$messageBody = sprintf("%s CONTEXT: this message is for testing purpose. In reality, it would be sent... <br />to: %s<br />%s%s<br />%s",
			strtoupper((string)GeneralUtility::getApplicationContext()),
			implode(',', array_keys($this->to)),
			empty($this->cc) ? '' : sprintf('cc: %s <br/>', implode(',', array_keys($this->cc))),
				empty($this->bbc) ? '' : sprintf('bcc: %s <br/>', implode(',', array_keys($this->bcc))),
				empty($this->replyTo) ? '' : sprintf('Reply-To: %s <br/>', implode(',', array_keys($this->replyTo))),
			$messageBody
		);
		return $messageBody;
	}

	/**
	 * Get the recipients whe email is not in production.
	 *
	 * @throws \Exception
	 * @return array
	 */
	protected function getRecipientsForDevelopmentContext() {
		$applicationContext = strtolower((string)GeneralUtility::getApplicationContext());
		if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL'][$applicationContext]['recipients'])) {
			$message = sprintf('I could not find development recipients. Missing value for $GLOBALS[\'TYPO3_CONF_VARS\'][\'MAIL\'][\'%s\'][\'recipients\']',
				strtolower($applicationContext)
			);
			throw new \Exception($message, 1402031636);
		}

		$emails = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['MAIL'][$applicationContext]['recipients']);

		$recipients = array();
		foreach ($emails as $email) {
			$recipients[$email] = $email;
		}
		$this->getEmailValidator()->validate($recipients);
		return $recipients;
	}

	/**
	 * Retrieves the message template object
	 *
	 * @return \Vanilla\Messenger\Domain\Model\Mailing
	 */
	public function getMessageTemplate() {
		return $this->messageTemplate;
	}

	/**
	 * Check whether a string contains HTML tags
	 *
	 * @see http://preprocess.me/how-to-check-if-a-string-contains-html-tags-in-php
	 * @param string $content the content to be analyzed
	 * @return boolean
	 */
	public function hasHtml($content) {
		$result = FALSE;
		//we compare the length of the string with html tags and without html tags
		if (strlen($content) != strlen(strip_tags($content))) {
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
	public function addAttachment($attachment) {

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
	 * @param mixed $values
	 * @return Message
	 */
	public function setMarkers($values) {
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
	public function addMarker($markerName, $value) {
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
	public function assignMultiple(array $values) {
		return $this->setMarkers($values);
	}

	/**
	 * Add a new maker.
	 *
	 * @param string $markerName
	 * @param mixed $value
	 * @return Message
	 * @deprecated
	 */
	public function assign($markerName, $value) {
		return $this->addMarker($markerName, $value);
	}

	/**
	 * @return int
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @param int $language
	 * @return Message
	 */
	public function setLanguage($language) {
		$this->language = $language;
		return $this;
	}

	/**
	 * @param mixed $recipients
	 * @return Message
	 * @deprecated as of 2.0 will be removed in 2 version
	 */
	public function setRecipients($recipients) {
		return $this->setTo($recipients);
	}

	/**
	 * Set "to" addresses. Should be an array('email' => 'name').
	 *
	 * @param mixed $addresses
	 * @return Message
	 */
	public function setTo($addresses) {
		$this->getEmailValidator()->validate($addresses);
		$this->to = $addresses;
		return $this;
	}

	/**
	 * Set "cc" addresses. Should be an array('email' => 'name').
	 *
	 * @param mixed $addresses
	 * @return Message
	 */
	public function setCc($addresses) {
		$this->getEmailValidator()->validate($addresses);
		$this->cc = $addresses;
		return $this;
	}

	/**
	 * Set "cc" addresses. Should be an array('email' => 'name').
	 *
	 * @param mixed $addresses
	 * @return Message
	 */
	public function setBcc($addresses) {
		$this->getEmailValidator()->validate($addresses);
		$this->bcc = $addresses;
		return $this;
	}

	/**
	 * Set "reply-to" addresses. Should be an array('email' => 'name').
	 *
	 * @param mixed $addresses
	 * @return Message
	 */
	public function setReplyTo($addresses) {
		$this->getEmailValidator()->validate($addresses);
		$this->replyTo = $addresses;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * Re-set default sender
	 *
	 * @param array $sender
	 * @return Message
	 */
	public function setSender(array $sender) {
		$this->getEmailValidator()->validate($this->sender);
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @return \Vanilla\Messenger\Domain\Model\MessageLayout
	 */
	public function getMessageLayout() {
		return $this->messageLayout;
	}

	/**
	 * parameter $messageLayout can be:
	 *      + \Vanilla\Messenger\Domain\Model\MessageLayout $messageLayout
	 *      + int $messageLayout which corresponds to an uid
	 *      + string $messageLayout which corresponds to a value for property "identifier".
	 *
	 * @throws RecordNotFoundException
	 * @param mixed $messageLayout
	 * @return Message
	 */
	public function setMessageLayout($messageLayout) {

		if ($messageLayout instanceof MessageLayout) {
			$this->messageLayout = $messageLayout;
		} else {

			// try to convert message layout to a possible uid.
			if ((int) $messageLayout > 0) {
				$messageLayout = (int) $messageLayout;
			}
			$methodName = is_int($messageLayout) ? 'findByUid' : 'findByQualifier';
			$this->messageLayout = $this->messageLayoutRepository->$methodName($messageLayout);

			if (is_null($this->messageLayout)) {
				$message = sprintf('I could not find message layout ""', $messageLayout);
				throw new RecordNotFoundException($message, 1389769449);
			}
		}

		return $this;
	}

	/**
	 * parameter $messageTemplate can be:
	 *      + \Vanilla\Messenger\Domain\Model\MessageTemplate $messageTemplate
	 *      + int $messageTemplate which corresponds to an uid
	 *      + string $messageTemplate which corresponds to a value for property "identifier".
	 *
	 * @throws RecordNotFoundException
	 * @param mixed $messageTemplate
	 * @return Message
	 */
	public function setMessageTemplate($messageTemplate) {

		if ($messageTemplate instanceof MessageTemplate) {
			$this->messageTemplate = $messageTemplate;
		} else {

			// try to convert message template to a possible uid.
			if ((int) $messageTemplate > 0) {
				$messageTemplate = (int) $messageTemplate;
			}
			$methodName = is_int($messageTemplate) ? 'findByUid' : 'findByQualifier';

			/** @var \Vanilla\Messenger\Domain\Model\MessageTemplate $messageTemplate */
			$messageTemplate = $this->messageTemplateRepository->$methodName($messageTemplate);
			if (is_object($this->getMessageLayout())) {
				$messageTemplate->setMessageLayout($this->getMessageLayout());
			}

			if (is_null($messageTemplate)) {
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
	protected function isMessagePrepared() {
		return !empty($this->mailMessage);
	}

	/**
	 * Convert this object to an array.
	 *
	 * @return array
	 */
	public function toArray() {

		if (! $this->isMessagePrepared()) {
			$this->prepareMessage();
		}

		$values = array(
			'sender' => $this->formatAddresses($this->getSender()),
			'recipient' => $this->formatAddresses($this->to), // @todo change me! recipient has been deprecated in favor of "to".
			'to' => $this->formatAddresses($this->to),
			'cc' => $this->formatAddresses($this->cc),
			'bcc' => $this->formatAddresses($this->bcc),
			'reply_to' => $this->formatAddresses($this->replyTo),
			'subject' => $this->getMailMessage()->getSubject(),
			'body' => $this->getMailMessage()->getBody(),
			'attachment' => count($this->getMailMessage()->getChildren()),
			'context' => (string)GeneralUtility::getApplicationContext(),
			'was_opened' => 0,
			'message_template' => $this->messageTemplate->getUid(),
			'message_layout' => is_object($this->messageLayout) ? $this->messageLayout->getUid() : 0,
			'sent_time' => time(),
			'mailing' => is_object($this->mailing) ? $this->mailing->getUid() : 0,
		);

		return $values;
	}

	/**
	 * Format an array of addresses
	 *
	 * @param array $addresses
	 * @return string
	 */
	protected function formatAddresses(array $addresses) {
		$formattedAddresses = array();
		foreach ($addresses as $email => $name) {
			$formattedAddresses[] = sprintf('%s <%s>', $name, $email);
		}
		return implode(', ', $formattedAddresses);

	}

	/**
	 * @return \TYPO3\CMS\Core\Mail\MailMessage
	 */
	public function getMailMessage() {
		if (is_null($this->mailMessage)) {
			$this->mailMessage = $this->objectManager->get('TYPO3\CMS\Core\Mail\MailMessage');
		}
		return $this->mailMessage;
	}

	/**
	 * @return \Vanilla\Messenger\Domain\Model\Mailing
	 */
	public function getMailing() {
		return $this->mailing;
	}

	/**
	 * @param \Vanilla\Messenger\Domain\Model\Mailing $mailing
	 */
	public function setMailing($mailing) {
		$this->mailing = $mailing;
	}

	/**
	 * @return \Vanilla\Messenger\Validator\EmailValidator
	 */
	public function getEmailValidator() {
		return GeneralUtility::makeInstance('Vanilla\Messenger\Validator\EmailValidator');
	}

	/**
	 * @return \Vanilla\Messenger\ContentRenderer\ContentRendererInterface
	 */
	public function getContentRenderer() {

		if ($this->isFrontendMode()) {
			/** @var \Vanilla\Messenger\ContentRenderer\FrontendRenderer $contentRenderer */
			$contentRenderer = GeneralUtility::makeInstance('Vanilla\Messenger\ContentRenderer\FrontendRenderer', $this->messageTemplate);
		} else {
			/** @var \Vanilla\Messenger\ContentRenderer\BackendRenderer $contentRenderer */
			$contentRenderer = GeneralUtility::makeInstance('Vanilla\Messenger\ContentRenderer\BackendRenderer');
		}
		return $contentRenderer;
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
