<?php

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

/**
 *
 *
 * @package messenger
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @todo remove language handling from the class which should be managed outside - or not?
 *
 */
class Tx_Messenger_Domain_Model_Message {

	/**
	 * @var int
	 */
	protected $uid;

	/**
	 * @var t3lib_mail_Message
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
	 * @var Tx_Messenger_Validator_Email
	 */
	protected $emailValidator;

	/**
	 * @var Tx_Messenger_Utility_Marker
	 */
	protected $markerUtility;

	/**
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
	 * @var Tx_Messenger_Domain_Repository_MessageTemplateRepository
	 */
	protected $templateRepository;

	/**
	 * @var Tx_Messenger_Domain_Model_MessageTemplate
	 */
	protected $messageTemplate;

	/**
	 * @var string
	 */
	protected $messageSubject;

	/**
	 * @var string
	 */
	protected $messageBody;

	/**
	 * @var Tx_Messenger_Utility_Configuration
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Messenger_Utility_Context
	 */
	protected $context;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->message = t3lib_div::makeInstance('t3lib_mail_Message');
		$this->templateRepository = t3lib_div::makeInstance('Tx_Messenger_Domain_Repository_MessageTemplateRepository');
		$this->emailValidator = t3lib_div::makeInstance('Tx_Messenger_Validator_Email');
		$this->markerUtility = t3lib_div::makeInstance('Tx_Messenger_Utility_Marker');
		$this->configurationManager = Tx_Messenger_Utility_Configuration::getInstance();
		$this->context = Tx_Messenger_Utility_Context::getInstance();

		$this->sender = array(
			$this->configurationManager->get('senderEmail') => $this->configurationManager->get('senderName')
		);

		$this->emailValidator->validate($this->sender);
	}

	/**
	 * Utility method that will fetch an email template and format its content according to a set of markers.
	 * Send the email eventually.
	 *
	 * @throws Tx_Messenger_Exception_WrongPluginConfigurationException
	 * @throws Tx_Messenger_Exception_MissingPropertyValueInMessageObjectException
	 * @return boolean whether or not the email was sent successfully
	 */
	public function send() {

		// Substitute markers
		$messageTemplate = $this->getMessageTemplate();
		if (empty($messageTemplate)) {
			throw new Tx_Messenger_Exception_MissingPropertyValueInMessageObjectException('Message template was not defined', 1354536584);
		}

		$recipients = $this->getRecipients();
		if (empty($recipients)) {
			throw new Tx_Messenger_Exception_MissingPropertyValueInMessageObjectException('Recipients was not defined', 1354536585);
		}

		$subject = $this->markerUtility->substitute($messageTemplate->getSubject(), $this->getMarkers(), 'text/plain');
		#$body = $this->markerUtility->substitute($messageTemplate->getBody(), $this->getMarkers());
		$body = $messageTemplate->getBody();

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
		if ($this->hasHtml($body)) {
			$text = Tx_Messenger_Utility_Html2Text::getInstance()->convert($body);
			$this->message->addPart($text, 'text/plain');
		}

		// Handle attachment
		foreach ($this->attachments as $attachment) {
			$this->message->attach($attachment);
		}

		$this->message->send();
		$result = $this->message->isSent();

		if (!$result) {
			throw new Tx_Messenger_Exception_WrongPluginConfigurationException('No Email sent, something went wrong. Check Swift Mail configuration', 1350124220);
		}
		return $result;
	}

	/**
	 * Get a body message when email is simulated.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function getMessageBodyForSimulation($content) {
		$messageBody = sprintf("%s CONTEXT: this message is a simulation.... In reality, it will be sent to %s <br /><br />%s",
			strtoupper($this->context->getName()),
			$this->configurationManager->get('developmentEmails'),
			$content
		);
		return $messageBody;
	}

	/**
	 * Get the recipients whe email is simulated.
	 *
	 * @return array
	 */
	protected function getRecipientsForSimulation() {
		$emails = t3lib_div::trimExplode(',', $this->configurationManager->get('developmentEmails'));

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
	 * @throws Tx_Messenger_Exception_RecordNotFoundException
	 * @return Tx_Messenger_Domain_Model_MessageTemplate
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
	 * @throws Tx_Messenger_Exception_MissingFileException
	 * @param string $attachment an absolute path to a file
	 * @return Tx_Messenger_Domain_Model_Message
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
			throw new Tx_Messenger_Exception_MissingFileException($message, 1350124207);
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function getMarkers() {
		return $this->markers;
	}

	/**
	 * @param array $markers
	 * @return Tx_Messenger_Domain_Model_Message
	 */
	public function setMarkers($markers) {
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
	 * @return Tx_Messenger_Domain_Model_Message
	 */
	public function setLanguage($language) {
		$this->context->setLanguage($language);
		return $this;
	}

	/**
	 * @param boolean $simulate
	 * @return Tx_Messenger_Domain_Model_Message
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
	 * @return Tx_Messenger_Domain_Model_Message
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
	 * @return Tx_Messenger_Domain_Model_Message
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
	 * @return Tx_Messenger_Domain_Model_Message
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
	 *      + Tx_Messenger_Domain_Model_MessageTemplate $messageTemplate
	 *      + int $messageTemplate which corresponds to an uid
	 *      + string $messageTemplate which corresponds to a value for property "identifier".
	 *
	 * @throws Tx_Messenger_Exception_RecordNotFoundException
	 * @param mixed $messageTemplate
	 * @return Tx_Messenger_Domain_Model_Message
	 */
	public function setMessageTemplate($messageTemplate) {


		if ($messageTemplate instanceof Tx_Messenger_Domain_Model_MessageTemplate) {
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
			throw new Tx_Messenger_Exception_RecordNotFoundException($message, 1350124207);
		}

		$this->messageTemplate = $object;
		return $this;
	}
}

?>