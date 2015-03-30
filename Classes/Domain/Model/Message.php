<?php
namespace Fab\Messenger\Domain\Model;

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

// For TYPO3 6.X or TYPO3 7.X, make sure Swift's auto-loader is registered
$swift1 = PATH_typo3 . 'contrib/swiftmailer/swift_required.php';
$swift2 = PATH_typo3 . 'contrib/swiftmailer/lib/swift_required.php';

if (is_readable($swift1)) {
    require_once $swift1;
} elseif (is_readable($swift2)) {
    require_once $swift2;
}

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
	 * @var \Fab\Messenger\Domain\Model\MessageLayout
	 */
	protected $messageLayout;

	/**
	 * @var \Fab\Messenger\Domain\Model\Mailing
	 */
	protected $mailing;

	/**
	 * @var array
	 */
	protected $attachments = array();

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
	 * @var \TYPO3\CMS\Core\Mail\MailMessage
	 */
	protected $mailMessage;

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

		// Parse Markdown only if necessary
		if ($this->messageTemplate->getTemplateEngine() === TemplateEngine::FLUID_AND_MARKDOWN) {
			$body = Markdown::defaultTransform($body);
		}

		$this->getMailMessage()->setTo($this->getTo())
			->setCc($this->getCc())
			->setBcc($this->getBcc())
			->setFrom($this->getSender())
			->setReplyTo($this->getReplyTo())
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
	 * Retrieves the message template object
	 *
	 * @return \Fab\Messenger\Domain\Model\Mailing
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
	 * Return "to" addresses.
	 * Special case: override "to" if a redirection has been set for a Context.
	 *
	 * @return array
	 */
	public function getTo() {
		return $this->to;
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
	 * Return "cc" addresses.
	 * Special case: there is no "cc" if a redirection has been set for a Context.
	 *
	 * @return array
	 */
	public function getCc() {
		return $this->cc;
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
	 * Return "bcc" addresses.
	 * Special case: there is no "bcc" if a redirection has been set for a Context.
	 *
	 * @return array
	 */
	public function getBcc() {
		return $this->bcc;
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
	 * @return array
	 */
	public function getReplyTo() {
		return $this->replyTo;
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
	 * @throws \Exception
	 * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
	 */
	public function getSender() {

		// Compute sender from global configuration.
		if (empty($this->sender)) {
			if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'])) {
				throw new \Exception('I could not find a sender email address. Missing value for "defaultMailFromAddress"', 1402032685);
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
	 */
	public function setSender(array $sender) {
		$this->getEmailValidator()->validate($sender);
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @return \Fab\Messenger\Domain\Model\MessageLayout
	 */
	public function getMessageLayout() {
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
	 *      + \Fab\Messenger\Domain\Model\MessageTemplate $messageTemplate
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

			/** @var \Fab\Messenger\Domain\Model\MessageTemplate $messageTemplate */
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
			'recipient' => $this->formatAddresses($this->getTo()), // @todo change me! recipient has been deprecated in favor of "to".
			'to' => $this->formatAddresses($this->getTo()),
			'cc' => $this->formatAddresses($this->getCc()),
			'bcc' => $this->formatAddresses($this->getBcc()),
			'reply_to' => $this->formatAddresses($this->getReplyTo()),
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
	 * @return \Fab\Messenger\Domain\Model\Mailing
	 */
	public function getMailing() {
		return $this->mailing;
	}

	/**
	 * @param \Fab\Messenger\Domain\Model\Mailing $mailing
	 */
	public function setMailing($mailing) {
		$this->mailing = $mailing;
	}

	/**
	 * @return \Fab\Messenger\Validator\EmailValidator
	 */
	public function getEmailValidator() {
		return GeneralUtility::makeInstance('Fab\Messenger\Validator\EmailValidator');
	}

	/**
	 * @return \Fab\Messenger\ContentRenderer\ContentRendererInterface
	 */
	public function getContentRenderer() {

		if ($this->isFrontendMode()) {
			/** @var \Fab\Messenger\ContentRenderer\FrontendRenderer $contentRenderer */
			$contentRenderer = GeneralUtility::makeInstance('Fab\Messenger\ContentRenderer\FrontendRenderer', $this->messageTemplate);
		} else {
			/** @var \Fab\Messenger\ContentRenderer\BackendRenderer $contentRenderer */
			$contentRenderer = GeneralUtility::makeInstance('Fab\Messenger\ContentRenderer\BackendRenderer');
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
