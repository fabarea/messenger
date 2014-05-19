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

use Swift_Attachment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Vanilla\Messenger\Exception\MissingFileException;
use Vanilla\Messenger\Exception\MissingPropertyValueInMessageObjectException;
use Vanilla\Messenger\Exception\RecordNotFoundException;
use Vanilla\Messenger\Exception\WrongPluginConfigurationException;
use Vanilla\Messenger\MessageStorage;
use Vanilla\Messenger\Service\LoggerService;
use Vanilla\Messenger\Utility\Algorithms;
use Vanilla\Messenger\Utility\Configuration;
use Vanilla\Messenger\Utility\Context;
use Vanilla\Messenger\Service\Html2Text;
use Vanilla\Messenger\Utility\ServerUtility;
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
	 * The "cc" addresses
	 *
	 * @var array
	 */
	protected $bcc = array();

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
	 * @var boolean
	 */
	protected $simulate = FALSE;

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
	 * @var Configuration
	 */
	protected $configurationManager;

	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * @var \Vanilla\Messenger\Service\Crawler
	 * @inject
	 */
	protected $crawler;

	/**
	 * @var \TYPO3\CMS\Core\Mail\MailMessage
	 */
	protected $mailMessage;

	/**
	 * Constructor
	 */
	public function __construct() {
		// @todo simplify
		$this->configurationManager = Configuration::getInstance();
		$this->context = Context::getInstance();

		$this->sender = array(
			$this->configurationManager->get('senderEmail') => $this->configurationManager->get('senderName')
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
	 * @throws MissingPropertyValueInMessageObjectException
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
	 * @throws MissingPropertyValueInMessageObjectException
	 * @return boolean whether or not the email was sent successfully
	 */
	protected function prepareMessage() {

		// Substitute markers
		if (empty($this->messageTemplate)) {
			throw new MissingPropertyValueInMessageObjectException('Messenger: message template was not defined', 1354536584);
		}

		if (empty($this->to)) {
			throw new MissingPropertyValueInMessageObjectException('Messenger: no recipient was defined', 1354536585);
		}

		$subject = $this->getMarkerUtility()->substitute($this->messageTemplate->getSubject(), $this->markers, 'text/plain');
		$body = $this->formatBody();

		// Set debug flag for not production context
		if ($this->context->isContextNotSendingEmails() || $this->simulate) {
			$body = $this->getMessageBodyForSimulation($body);
			$this->to = $this->getRecipientsForSimulation();
		}
		#$body = $this->markerUtility->substitute($body, $this->markers);
		$body = Markdown::defaultTransform($body);

		$this->getMailMessage()->setTo($this->to)
			->setFrom($this->sender)
			->setSubject($subject)
			->setBody($body, 'text/html');

		// Add possible CC
		if (!empty($this->cc)) {
			$this->getMailMessage()->setCc($this->cc);
		}

		// Add possible BCC
		if (!empty($this->bcc)) {
			$this->getMailMessage()->setBcc($this->bcc);
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
	 * Format the body by fetching content from the FE.
	 * This is required in order to "resolve" the View Helpers in the context of Fluid.
	 *
	 * @return string
	 */
	protected function formatBody() {

		$registryIdentifier = Algorithms::generateUUID();
		$registryEntry = array(
			'messageBody' => $this->messageTemplate->getBody(),
			'markers' => $this->markers,
		);

		$this->getRegistry()->set('Vanilla\Messenger', $registryIdentifier, $registryEntry);

		$this->crawler->addGetVar('type', 1370537883)
			->addGetVar('tx_messenger_pi1[registryIdentifier]', $registryIdentifier)
			->setUrl(ServerUtility::getHostAndProtocol());

		//echo $this->crawler->getFinalUrl(); exit();
		$formattedBody = $this->crawler->exec();
		return trim($formattedBody);
	}

	/**
	 * Get a body message when email is simulated.
	 *
	 * @param string $messageBody
	 * @return string
	 */
	protected function getMessageBodyForSimulation($messageBody) {
		$messageBody = sprintf("%s CONTEXT: this message is for testing purposes.... In reality it would be sent to %s <br /><br />%s",
			strtoupper($this->context->getName()),
			implode(',', array_keys($this->to)),
			$messageBody
		);
		return $messageBody;
	}

	/**
	 * Get the recipients whe email is simulated.
	 *
	 * @return array
	 */
	protected function getRecipientsForSimulation() {
		$emails = GeneralUtility::trimExplode(',', $this->configurationManager->get('developmentEmails'));

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
	 * Extract Fluid marker from the body or the subject source. A Fluid marker is formatted as follows {foo} .
	 *
	 * @param string $messagePart can be Message::BODY or Message::SUBJECT
	 * @throws \RuntimeException
	 * @return array
	 */
	public function extractMakersFromTemplate($messagePart = '') {

		if (empty($this->messageTemplate)) {
			throw new \RuntimeException('Messenger: message template was not defined', 1400511070);
		}

		if ($messagePart === self::SUBJECT) {
			$content = $this->messageTemplate->getSubject();
		} elseif ($messagePart === self::BODY) {
			$content = $this->messageTemplate->getBody();
		} else {
			$content = $this->messageTemplate->getSubject();
			$content .= $this->messageTemplate->getBody();
		}

		/** @var \TYPO3\CMS\Fluid\Core\Parser\TemplateParser $templateParser */
		$templateParser = $this->objectManager->get('TYPO3\CMS\Fluid\Core\Parser\TemplateParser');
		$parsedTemplate = $templateParser->parse($content);

		$markers = array();
		/** @var \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode $node */
		foreach ($parsedTemplate->getRootNode()->getChildNodes() as $node) {
			if ($node instanceof \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode) {
				$markers[] = $node->getObjectPath();
			}
		}

		return $markers;
	}

	/**
	 * Add an attachment.
	 *
	 * @throws MissingFileException
	 * @param string $attachment an absolute path to a file
	 * @return \Vanilla\Messenger\Domain\Model\Message
	 */
	public function addAttachment($attachment) {

		// Makes sure the file exist
		if (is_file($attachment)) {
			$parts = explode('/', $attachment);
			$fileName = array_pop($parts);
			$this->attachments[] = Swift_Attachment::fromPath($attachment)->setFilename($fileName);
		} else {
			$message = sprintf('File not found "%s"', $attachment);
			throw new MissingFileException($message, 1389779394);
		}
		return $this;
	}

	/**
	 * Set Markers
	 *
	 * @param mixed $markers
	 * @return \Vanilla\Messenger\Domain\Model\Message
	 */
	public function setMarkers($markers) {
		$this->markers = $markers;
		return $this;
	}

	/**
	 * Add a new maker.
	 *
	 * @param string $markerName
	 * @param mixed $value
	 * @return \Vanilla\Messenger\Domain\Model\Message
	 */
	public function addMarker($markerName, $value) {
		$this->markers[$markerName] = $value;
		return $this;
	}

	/**
	 * Returns an instance of the Frontend object.
	 *
	 * @return \TYPO3\CMS\Core\Registry
	 */
	protected function getRegistry() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');
	}

	/**
	 * @return int
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @param int $language
	 * @return \Vanilla\Messenger\Domain\Model\Message
	 */
	public function setLanguage($language) {
		$this->context->setLanguage($language);
		return $this;
	}

	/**
	 * @param boolean $simulate
	 * @return \Vanilla\Messenger\Domain\Model\Message
	 */
	public function simulate($simulate = TRUE) {
		$this->simulate = $simulate;
		return $this;
	}

	/**
	 * @param mixed $recipients
	 * @return \Vanilla\Messenger\Domain\Model\Message
	 * @deprecated as of 2.0 will be removed in 2 version
	 */
	public function setRecipients($recipients) {
		return $this->setTo($recipients);
	}

	/**
	 * Set "to" addresses. Should be an array('email' => 'name').
	 *
	 * @param mixed $addresses
	 * @return \Vanilla\Messenger\Domain\Model\Message
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
	 * @return \Vanilla\Messenger\Domain\Model\Message
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
	 * @return \Vanilla\Messenger\Domain\Model\Message
	 */
	public function setBcc($addresses) {
		$this->getEmailValidator()->validate($addresses);
		$this->bcc = $addresses;
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
	 * @return \Vanilla\Messenger\Domain\Model\Message
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
	 * @return \Vanilla\Messenger\Domain\Model\Message
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
	 * @return \Vanilla\Messenger\Domain\Model\Message
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
			'subject' => $this->getMailMessage()->getSubject(),
			'body' => $this->getMailMessage()->getBody(),
			'attachment' => count($this->getMailMessage()->getChildren()),
			'context' => $this->context->getName(),
			'was_opened' => 0,
			'message_template' => $this->messageTemplate->getUid(),
			'message_layout' => is_object($this->messageLayout) ? $this->messageLayout->getUid() : 0,
			'sent_time' => time(),
			'mailing' => is_object($this->mailing) ? $this->mailing->getUid() : 0,
			'body_crawler_url' => $this->crawler->getFinalUrl(),
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
	 * @return \Vanilla\Messenger\Utility\MarkerUtility
	 */
	public function getMarkerUtility() {
		return GeneralUtility::makeInstance('Vanilla\Messenger\Utility\MarkerUtility');
	}
}
