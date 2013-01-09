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
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var t3lib_mail_Message
	 */
	protected $message;

	/**
	 * @var array
	 */
	protected $settings = array('senderEmail' => 'john@doe.com', 'senderName' => 'John Doe');

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
	protected $markers;

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
	protected $dryRun = FALSE;

	/**
	 * @var array
	 */
	protected $attachments = array();

	/**
	 * @var Tx_Messenger_Domain_Repository_MessageTemplateRepository
	 */
	protected $templateRepository;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->message = t3lib_div::makeInstance('t3lib_mail_Message');
		$this->templateRepository = t3lib_div::makeInstance('Tx_Messenger_Domain_Repository_MessageTemplateRepository');
		$this->emailValidator = t3lib_div::makeInstance('Tx_Messenger_Validator_Email');
		$this->markerUtility = t3lib_div::makeInstance('Tx_Messenger_Utility_Marker');

		if (isset($GLOBALS['TSFE'])) {
			$this->settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_messenger.']['settings.'];
		}

		$this->sender = array($this->settings['senderEmail'] => $this->settings['senderName']);
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

		$identifier = $this->getIdentifier();
		if (empty($identifier)) {
			throw new Tx_Messenger_Exception_MissingPropertyValueInMessageObjectException('Property "identifier" is not defined', 1354536584);
		}

		$recipients = $this->getRecipients();
		if (empty($recipients)) {
			throw new Tx_Messenger_Exception_MissingPropertyValueInMessageObjectException('Property "recipients" is not defined', 1354536585);
		}

		$templateObject = $this->getTemplateObject($identifier);

		// Substitute markers
		$subject = $this->markerUtility->substitute($templateObject->getSubject(), $this->getMarkers(), 'text/plain');
		$templateObject->setSubject($subject);

		$body = $this->markerUtility->substitute($templateObject->getBody(), $this->getMarkers());
		$templateObject->setBody($body);

		// Set debug flag for not production context
		if (! Tx_Messenger_Utility_Context::getInstance()->isContextSendingEmails()) {
			$this->setDryRun(TRUE);
		}
		$this->setDebug($this->getDryRun(), $templateObject);

		$this->message->setTo($this->recipients)
			->setFrom($this->sender)
			->setSubject($templateObject->getSubject())
			->setBody($templateObject->getBody(), 'text/html');

		// Attach plain text version if HTML tags are found in body
		if ($this->hasHtml($templateObject->getBody())) {
			$text = Tx_Messenger_Utility_Html2Text::getInstance()->convert($templateObject->getBody());
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
	 * Set debug mode.
	 * Visibility of the method set to public for unit testing cause of the passed object as reference.
	 * Method is considered as internal, though.
	 *
	 * @internal
	 * @param boolean $debug
	 * @param Tx_Messenger_Domain_Model_MessageTemplate $templateObject
	 * @return void
	 */
	public function setDebug($debug, Tx_Messenger_Domain_Model_MessageTemplate &$templateObject) {
		if ($debug) {
			$recipients = array($this->settings['debug.']['recipientEmail'] => $this->settings['debug.']['recipientName']);
			$recipientsList = '';
			$x = 0;
			foreach ($this->recipients AS $email => $name) {
				if ($x > 0) $recipientsList .= ', ';
				$recipientsList .= $name . ' (' . $email . ')';
				$x++;
			}
			$templateObject->setBody("DEBUG MODE : This message is send to debuggers... It should be send to  " . $recipientsList . "<br/>\n" . $templateObject->getBody());
			$this->emailValidator->validate($recipients);
			$this->recipients = $recipients;
		}
	}

	/**
	 * Retrieves the template object
	 *
	 * @throws Tx_Messenger_Exception_RecordNotFoundException
	 * @param string $identifier
	 * @return Tx_Messenger_Domain_Model_MessageTemplate
	 */
	protected function getTemplateObject($identifier) {

		/** @var $templateObject Tx_Messenger_Domain_Model_MessageTemplate */
		$templateObject = $this->templateRepository->findByIdentifier($identifier);

		if (!$templateObject) {
			$message = sprintf('No Email Template record was found for identity "%s"', $identifier);
			throw new Tx_Messenger_Exception_RecordNotFoundException($message, 1350124207);
		}

		// Attach a layout to the email template
		$templateObject->setLayout($this->getLayout());

		return $templateObject;
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
	 * Add an attachment
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
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param string $identifier
	 * @return Tx_Messenger_Domain_Model_Message
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
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
		Tx_Messenger_Utility_Context::getInstance()->setLanguage($language);
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getDryRun() {
		return $this->dryRun;
	}

	/**
	 * @param boolean $dryRun
	 * @return Tx_Messenger_Domain_Model_Message
	 */
	public function setDryRun($dryRun) {
		$this->dryRun = $dryRun;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getRecipients() {
		return $this->recipients;
	}

	/**
	 * Set recipients
	 *
	 * @param array $recipients
	 * @return Tx_Messenger_Domain_Model_Message
	 */
	public function setRecipients($recipients) {
		// normally should be a tag @validate...
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
}

?>