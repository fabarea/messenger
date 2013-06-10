<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@gebruederheitz.de>, Gebruederheitz
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
 */
class Tx_Messenger_Controller_BackendController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Messenger_Interface_ListableInterface
	 */
	protected $listManager;

	/**
	 * @var Tx_Messenger_Domain_Repository_MessageTemplateRepository
	 * @inject
	 */
	protected $messageTemplateRepository;

	/**
	 * Initializes the controller before invoking an action method.
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 */
	public function initializeAction() {

		try {
			$this->listManager = Tx_Messenger_ListManager_Factory::getInstance();

			/** @var $listManagerValidator Tx_Messenger_Validator_ListManagerValidator */
			$listManagerValidator = $this->objectManager->get('Tx_Messenger_Validator_ListManagerValidator');
			$listManagerValidator->validate($this->listManager);
		} catch(Exception $e ) {
			$this->flashMessageContainer->add('List Manager validation error: ' . $e->getMessage(), '', t3lib_FlashMessage::ERROR);
			$this->redirect('list', 'ListManager');
		}
	}

	/**
	 * send a message
	 *
	 * @param int $messageTemplateUid
	 * @param string $recipientUid
	 * @return void
	 */
	public function sendMessageAction($messageTemplateUid = 0, $recipientUid = '') {

		$result = 'The message could not be sent!';
		$status = 'error';
		if ($messageTemplateUid > 0) {

			$recipients = t3lib_div::trimExplode(',', $recipientUid);
			$mapping = $this->listManager->getMapping();

			$result = count($recipients);
			$status = 'success';

			foreach ($recipients as $recipientUid) {
				$recipient = $this->listManager->findByUid($recipientUid);

				/** @var $message Tx_Messenger_Domain_Model_Message */
				$message = $this->objectManager->get('Tx_Messenger_Domain_Model_Message');
				$isSent = $message->setMessageTemplate($messageTemplateUid)
					->setRecipients($this->formatRecipient($recipient, $mapping))
					->setMarkers($recipient)
					->send();

				// Block the loop if anything goes wrong.
				if (! $isSent) {
					$result = sprintf('The message could not be sent for recipient uid %s. It could be more error besides...', $recipientUid);
					$status = 'error';
					break;
				}
			}

		}
		$this->request->setFormat('json'); // I would have expected to send a json header... but not the case.
		header("Content-Type: text/json");
		return json_encode(array('message' => $result, 'status' => $status));
	}

	/**
	 * Format a recipient.
	 *
	 * Return recipient info according to an identifier. The returned array must look like:
	 * array('email' => 'recipient name');
	 */
	public function formatRecipient($recipient, $mapping) {
		$emailMapping = $mapping['email'];
		$nameMapping = $mapping['name'];
		if (is_array($recipient)) {
			$result = array($recipient[$emailMapping] => $recipient[$nameMapping]);
		} else {
			$getEmail = 'get' . ucfirst($emailMapping);
			$getName = 'get' . ucfirst($nameMapping);
			$result = array(call_user_func($recipient, $getEmail) => call_user_func($recipient, $getName));
		}
		return $result;
	}

	/**
	 * send a message for testing
	 *
	 * @param int $messageTemplateUid
	 * @param string $testEmail
	 * @return void
	 */
	public function sendMessageTestAction($messageTemplateUid = 0, $testEmail = '') {

		$result = 'The message could not be sent! Missing email?';
		$status = 'error';
		if ($messageTemplateUid > 0 && $testEmail != '') {

			/** @var $message Tx_Messenger_Domain_Model_Message */
			$message = $this->objectManager->get('Tx_Messenger_Domain_Model_Message');
			$isSent = $message->setMessageTemplate($messageTemplateUid)
				->setRecipients($testEmail)
				->send();

			// value "1" corresponds to the number of email sent
			$result = $isSent ? '1' : 'The message could not be sent! Contact an administrator.';
			$status = $isSent ? 'success' : $status;

			// save email address as preference
			Tx_Messenger_Utility_BeUserPreference::set('messenger_testing_email', $testEmail);
		}
		$this->request->setFormat('json'); // I would have expected to send a json header... but not the case.
		header("Content-Type: text/json");
		return json_encode(array('message' => $result, 'status' => $status));
	}

	/**
	 * @param Tx_Messenger_QueryElement_Matcher $matcher
	 * @param Tx_Messenger_QueryElement_Order $order
	 * @param Tx_Messenger_QueryElement_Pager $pager
	 * @return void
	 * @validate $matcher Tx_Messenger_Domain_Validator_MatcherValidator
	 */
	public function indexAction(Tx_Messenger_QueryElement_Matcher $matcher = NULL, Tx_Messenger_QueryElement_Order $order = NULL, Tx_Messenger_QueryElement_Pager $pager = NULL) {

		$matcher = $matcher === NULL ? $this->objectManager->get('Tx_Messenger_QueryElement_Matcher') : $matcher;
		$order = $order === NULL ? $this->objectManager->get('Tx_Messenger_QueryElement_Order') : $order;
		$pager = $pager === NULL ? $this->objectManager->get('Tx_Messenger_QueryElement_Pager') : $pager;

		$messageUid = Tx_Messenger_Utility_BeUserPreference::get('messenger_message_template');
		$messageTemplate = $this->messageTemplateRepository->findByUid($messageUid);

		$this->view->assign('recipients', $this->listManager->findBy($matcher, $order, $pager->getLimit(), $pager->getOffset()));
		$this->view->assign('filters', $this->listManager->getFilters());
		$this->view->assign('messageTemplate', $messageTemplate);
		$this->view->assign('matcher', $matcher);
		$this->view->assign('order', $order);
		$this->view->assign('pager', $pager);
	}
}
?>