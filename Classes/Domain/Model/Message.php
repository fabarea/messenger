<?php
namespace TYPO3\CMS\Messenger\Domain\Model;
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
use TYPO3\CMS\Messenger\Exception\MissingFileException;
use TYPO3\CMS\Messenger\Exception\MissingPropertyValueInMessageObjectException;
use TYPO3\CMS\Messenger\Exception\RecordNotFoundException;
use TYPO3\CMS\Messenger\Exception\WrongPluginConfigurationException;
use TYPO3\CMS\Messenger\Utility\Html2Text;
use TYPO3\CMS\Messenger\Utility\Object;
use TYPO3\CMS\Messenger\Utility\Server;
use \Michelf\Markdown;
/**
 * Message representation
 * @todo remove language handling from the class which should be managed outside - or not?
 */
class Message {

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
	 * @var array
	 */
	protected $recipients = array();

	/**
	 * @var \TYPO3\CMS\Messenger\Validator\Email
	 */
	protected $emailValidator;

	/**
	 * @var \TYPO3\CMS\Messenger\Utility\Marker
	 * @inject
	 */
	protected $markerUtility;

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
	 * @var \TYPO3\CMS\Messenger\Domain\Model\MessageLayout
	 */
	protected $messageLayout;

	/**
	 * @var \TYPO3\CMS\Messenger\Domain\Model\Mailing
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
	 * @var \TYPO3\CMS\Messenger\Domain\Repository\MessageTemplateRepository
	 * @inject
	 */
	protected $messageTemplateRepository;

	/**
	 * @var \TYPO3\CMS\Messenger\Domain\Repository\MessageLayoutRepository
	 * @inject
	 */
	protected $messageLayoutRepository;

	/**
	 * @var \TYPO3\CMS\Messenger\Domain\Repository\SentMessageRepository
	 * @inject
	 */
	protected $sentMessageRepository;

	/**
	 * @var \TYPO3\CMS\Messenger\Domain\Repository\QueueRepository
	 * @inject
	 */
	protected $queueRepository;

	/**
	 * @var \TYPO3\CMS\Messenger\Domain\Model\MessageTemplate
	 */
	protected $messageTemplate;

	/**
	 * @var \TYPO3\CMS\Messenger\Utility\Configuration
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Messenger\Utility\Context
	 */
	protected $context;

	/**
	 * @var \TYPO3\CMS\Messenger\Utility\Crawler
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
		$this->emailValidator = GeneralUtility::makeInstance('TYPO3\CMS\Messenger\Validator\Email');
		$this->configurationManager = \TYPO3\CMS\Messenger\Utility\Configuration::getInstance();
		$this->context = \TYPO3\CMS\Messenger\Utility\Context::getInstance();

		$this->sender = array(
			$this->configurationManager->get('senderEmail') => $this->configurationManager->get('senderName')
		);

		$this->emailValidator->validate($this->sender);
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
	 * Prepares the emails and queue it.
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
			$this->sentMessageRepository->add($this->toArray());
		} else {
			throw new WrongPluginConfigurationException('No Email sent, something went wrong. Check Swift Mail configuration', 1350124220);
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
			throw new MissingPropertyValueInMessageObjectException('Message template was not defined', 1354536584);
		}

		$recipients = $this->getRecipients();
		if (empty($recipients)) {
			throw new MissingPropertyValueInMessageObjectException('Recipients was not defined', 1354536585);
		}

		$subject = $this->markerUtility->substitute($this->messageTemplate->getSubject(), $this->getMarkers(), 'text/plain');

		$body = $this->formatBody();

		// Set debug flag for not production context
		if ($this->context->isContextNotSendingEmails() || $this->simulate) {
			$body = $this->getMessageBodyForSimulation($body);
			$recipients = $this->getRecipientsForSimulation();
		}
		$body = $this->markerUtility->substitute($body, $this->getMarkers());
		$body = Markdown::defaultTransform($body);

		$this->getMailMessage()->setTo($recipients)
			->setFrom($this->sender)
			->setSubject($subject)
			->setBody($body, 'text/html');

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
	 *
	 * @return string
	 */
	protected function formatBody() {

		// get body of message which get called by a crawler for resolving fluid syntax
		$this->crawler->addGetVar('type', 1370537883);
		$this->crawler->addGetVar('tx_messenger_pi1[messageTemplate]', $this->messageTemplate->getUid());

		foreach ($this->markers as $key => $value) {
			// send as post to avoid HTTP 414 "Request-URI Too Large"
			$this->crawler->addPostVar(sprintf('tx_messenger_pi1[markers][%s]', $key), $value);
		}

		$this->crawler->exec(Server::getHostAndProtocol());
		return $this->crawler->getResult();
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
			implode(',', array_keys($this->recipients)),
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
		$this->emailValidator->validate($recipients);
		return $recipients;
	}

	/**
	 * Retrieves the message template object
	 *
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Mailing
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
	 * Add an attachment.
	 *
	 * @throws MissingFileException
	 * @param string $attachment an absolute path to a file
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function addAttachment($attachment) {

		// Makes sure the file exist
		if (is_file($attachment)) {
			$parts = explode('/', $attachment);
			$fileName = array_pop($parts);
			$this->attachments[] = Swift_Attachment::fromPath($attachment)->setFilename($fileName);
		} else {
			$message = sprintf('File not found "%s"', $attachment);
			throw new MissingFileException($message, 1350124207);
		}
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMarkers() {
		return $this->markers;
	}

	/**
	 * The normal case is to pass an array to the setter. Though an object can be given which will be converted to an array eventually.
	 *
	 * @param mixed $markers
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function setMarkers($markers) {
		if (is_object($markers)) {
			$markers = Object::toArray($markers);
		}
		$this->markers = $markers;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @param int $language
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function setLanguage($language) {
		$this->context->setLanguage($language);
		return $this;
	}

	/**
	 * @param boolean $simulate
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function simulate($simulate = TRUE) {
		$this->simulate = $simulate;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getRecipients() {
		return $this->recipients;
	}

	/**
	 * Set recipients.
	 * Can be an array('email' => 'name') or an email address.
	 *
	 * @param mixed $recipients
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function setRecipients($recipients) {
		if (is_string($recipients)) {
			$recipients = array($recipients => $recipients);
		}
		// could be implemented as tag @validate...
		$this->emailValidator->validate($recipients);
		$this->recipients = $recipients;
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
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function setSender(array $sender) {
		$this->emailValidator->validate($this->sender);
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @return \TYPO3\CMS\Messenger\Domain\Model\MessageLayout
	 */
	public function getMessageLayout() {
		return $this->messageLayout;
	}

	/**
	 * parameter $messageLayout can be:
	 *      + \TYPO3\CMS\Messenger\Domain\Model\MessageLayout $messageLayout
	 *      + int $messageLayout which corresponds to an uid
	 *      + string $messageLayout which corresponds to a value for property "identifier".
	 *
	 * @throws RecordNotFoundException
	 * @param mixed $messageLayout
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function setMessageLayout($messageLayout) {

		if ($messageLayout instanceof MessageLayout) {
			$this->messageLayout = $messageLayout;
		} else {

			// try to convert message layout to a possible uid.
			if ((int) $messageLayout > 0) {
				$messageLayout = (int) $messageLayout;
			}
			$methodName = is_int($messageLayout) ? 'findByUid' : 'findBySpeakingIdentifier';
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
	 *      + \TYPO3\CMS\Messenger\Domain\Model\MessageTemplate $messageTemplate
	 *      + int $messageTemplate which corresponds to an uid
	 *      + string $messageTemplate which corresponds to a value for property "identifier".
	 *
	 * @throws RecordNotFoundException
	 * @param mixed $messageTemplate
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function setMessageTemplate($messageTemplate) {

		if ($messageTemplate instanceof MessageTemplate) {
			$this->messageTemplate = $messageTemplate;
		} else {

			// try to convert message template to a possible uid.
			if ((int) $messageTemplate > 0) {
				$messageTemplate = (int) $messageTemplate;
			}
			$methodName = is_int($messageTemplate) ? 'findByUid' : 'findBySpeakingIdentifier';

			$this->messageTemplate = $this->messageTemplateRepository->$methodName($messageTemplate);

			if (is_object($this->getMessageLayout())) {
				$this->messageTemplate->setMessageTemplate($this->getMessageLayout());
			}

			if (is_null($this->messageTemplate)) {
				$message = sprintf('I could not find message template ""', $messageTemplate);
				throw new RecordNotFoundException($message, 1350124207);
			}
		}

		return $this;
	}

	/**
	 * Convert this object to an array.
	 *
	 * @return array
	 */
	public function toArray() {

		// Prepare recipients
		$recipients = array();
		foreach ($this->getRecipients() as $email => $name) {
			$recipients[] = sprintf('%s <%s>', $name, $email);
		}

		// Prepare recipients
		$senders = array();
		foreach ($this->getSender() as $email => $name) {
			$senders[] = sprintf('%s <%s>', $name, $email);
		}

		$values = array(
			'sender' => implode(',', $senders),
			'recipient' => implode(',', $recipients),
			'subject' => $this->getMailMessage()->getSubject(),
			'body' => $this->getMailMessage()->getBody(),
			'attachment' => count($this->getMailMessage()->getChildren()),
			'context' => $this->context->getName(),
			'was_opened' => 0,
			'message_template' => $this->messageTemplate->getUid(),
			'message_layout' => is_object($this->messageLayout) ? $this->messageLayout->getUid() : 0,
			'sent_time' => time(),
			'mailing' => is_object($this->mailing) ? $this->mailing->getUid() : 0,
		);

		return $values;
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
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Mailing
	 */
	public function getMailing() {
		return $this->mailing;
	}

	/**
	 * @param \TYPO3\CMS\Messenger\Domain\Model\Mailing $mailing
	 */
	public function setMailing($mailing) {
		$this->mailing = $mailing;
	}
}

?>