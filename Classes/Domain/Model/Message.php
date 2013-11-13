<?php
namespace TYPO3\CMS\Messenger\Domain\Model;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Fabien Udriot <fudriot@cobweb.ch>, Cobweb
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 * @todo remove language handling from the class which should be managed outside - or not?
 *
 */
class Message {

	/**
	 * @var int
	 */
	protected $uid;

	/**
	 * @var \TYPO3\CMS\Core\Mail\MailMessage
	 */
	protected $message;

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
	 * @var string
	 */
	protected $layout;

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
	 */
	protected $templateRepository;

	/**
	 * @var \TYPO3\CMS\Messenger\Domain\Model\MessageTemplate
	 */
	protected $template;

	/**
	 * @var string
	 */
	protected $messageSubject;

	/**
	 * @var string
	 */
	protected $messageBody;

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
	 */
	protected $crawler;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->message = GeneralUtility::makeInstance('TYPO3\CMS\Core\Mail\MailMessage');
		$this->templateRepository = GeneralUtility::makeInstance('TYPO3\CMS\Messenger\Domain\Repository\MessageTemplateRepository');
		$this->emailValidator = GeneralUtility::makeInstance('TYPO3\CMS\Messenger\Validator\Email');
		$this->markerUtility = GeneralUtility::makeInstance('TYPO3\CMS\Messenger\Utility\Marker');
		$this->crawler = GeneralUtility::makeInstance('TYPO3\CMS\Messenger\Utility\Crawler');
		$this->configurationManager = \TYPO3\CMS\Messenger\Utility\Configuration::getInstance();
		$this->context = \TYPO3\CMS\Messenger\Utility\Context::getInstance();

		$this->sender = array(
			$this->configurationManager->get('senderEmail') => $this->configurationManager->get('senderName')
		);

		$this->emailValidator->validate($this->sender);
	}

	/**
	 * Utility method that will fetch an email template and format its content according to a set of markers.
	 * Send the email eventually.
	 *
	 * @throws \TYPO3\CMS\Messenger\Exception\WrongPluginConfigurationException
	 * @throws \TYPO3\CMS\Messenger\Exception\MissingPropertyValueInMessageObjectException
	 * @return boolean whether or not the email was sent successfully
	 */
	public function send() {

		// Substitute markers
		if (empty($this->template)) {
			throw new \TYPO3\CMS\Messenger\Exception\MissingPropertyValueInMessageObjectException('Message template was not defined', 1354536584);
		}

		$recipients = $this->getRecipients();
		if (empty($recipients)) {
			throw new \TYPO3\CMS\Messenger\Exception\MissingPropertyValueInMessageObjectException('Recipients was not defined', 1354536585);
		}

		$subject = $this->markerUtility->substitute($this->template->getSubject(), $this->getMarkers(), 'text/plain');

		$body = $this->formatBody();

		// Set debug flag for not production context
		if ($this->context->isContextNotSendingEmails() || $this->simulate) {
			$body = $this->getMessageBodyForSimulation($body);
			$recipients = $this->getRecipientsForSimulation();
		}
		$body = $this->markerUtility->substitute($body, $this->getMarkers());

		$this->message->setTo($recipients)
			->setFrom($this->sender)
			->setSubject($subject)
			->setBody($body, 'text/html');

		// Attach plain text version if HTML tags are found in body
		if ($this->hasHtml($body) && \TYPO3\CMS\Messenger\Utility\Configuration::getInstance()->get('sendMultipartedEmail')) {
			$text = \TYPO3\CMS\Messenger\Utility\Html2Text::getInstance()->convert($body);
			$this->message->addPart($text, 'text/plain');
		}

		// Handle attachment
		foreach ($this->attachments as $attachment) {
			$this->message->attach($attachment);
		}

		$this->message->send();
		$result = $this->message->isSent();

		if (!$result) {
			throw new \TYPO3\CMS\Messenger\Exception\WrongPluginConfigurationException('No Email sent, something went wrong. Check Swift Mail configuration', 1350124220);
		}
		return $result;
	}

	/**
	 * Format the body by fetching content from the FE.
	 *
	 * @return string
	 */
	protected function formatBody() {

		// get body of message which get called by a crawler for resolving fluid syntax
		$this->crawler->addGetVar('type', 1370537883);
		$this->crawler->addGetVar('tx_messenger_pi1[messageTemplate]', $this->template->getUid());

		foreach ($this->markers as $key => $value) {
			$this->crawler->addGetVar(sprintf('tx_messenger_pi1[markers][%s]', $key), $value);
		}

		$this->crawler->exec(\TYPO3\CMS\Messenger\Utility\Server::getHostAndProtocol());
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
	 * @throws \TYPO3\CMS\Messenger\Exception\RecordNotFoundException
	 * @return \TYPO3\CMS\Messenger\Domain\Model\MessageTemplate
	 */
	public function getTemplate() {
		return $this->template;
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
	 * @throws \TYPO3\CMS\Messenger\Exception\MissingFileException
	 * @param string $attachment an absolute path to a file
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function addAttachment($attachment) {

		// Makes sure the file exist
		if (is_file($attachment)) {
			$parts = explode('/', $attachment);
			$fileName = array_pop($parts);
			$this->attachments[] = Swift_Attachment::fromPath($attachment)->setFilename($fileName);
		}
		else {
			$message = sprintf('File not found "%s"', $attachment);
			throw new \TYPO3\CMS\Messenger\Exception\MissingFileException($message, 1350124207);
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
			$markers = \TYPO3\CMS\Messenger\Utility\Object::toArray($markers);
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
	 * @return string
	 */
	public function getLayout() {
		return $this->layout;
	}

	/**
	 * Corresponds to a layout identifier
	 *
	 * @param string $layout
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function setLayout($layout) {
		$this->layout = $layout;
		return $this;
	}

	/**
	 * Set a message template.
	 *
	 * Can take as parameter:
	 *
	 *      + \TYPO3\CMS\Messenger\Domain\Model\MessageTemplate $messageTemplate
	 *      + int $messageTemplate which corresponds to an uid
	 *      + string $messageTemplate which corresponds to a value for property "identifier".
	 *
	 * @throws \TYPO3\CMS\Messenger\Exception\RecordNotFoundException
	 * @param mixed $messageTemplate
	 * @return \TYPO3\CMS\Messenger\Domain\Model\Message
	 */
	public function setTemplate($messageTemplate) {


		if ($messageTemplate instanceof \TYPO3\CMS\Messenger\Domain\Model\MessageTemplate) {
			$object = $messageTemplate;
		} else {

			// try to convert message template to a possible uid.
			if ((int) $messageTemplate > 0) {
				$messageTemplate = (int) $messageTemplate;
			}
			$methodName = is_int($messageTemplate) ? 'findByUid' : 'findByIdentifier';
			$object = call_user_func_array(array($this->templateRepository, $methodName), array($messageTemplate));

			// Attach a layout to the email template
			// @todo: add setMessageLayout method()
			#$messageTemplate->setLayout($this->getLayout());
		}

		if (is_null($object)) {
			$message = sprintf('No Email Template record was found for identifier "%s"', $messageTemplate);
			throw new \TYPO3\CMS\Messenger\Exception\RecordNotFoundException($message, 1350124207);
		}

		$this->template = $object;
		return $this;
	}
}

?>