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
	 */
	protected $messageTemplateRepository;

	/**
	 * @param Tx_Messenger_Domain_Repository_MessageTemplateRepository $messageTemplateRepository
	 * @return void
	 */
	public function injectMessageTemplateRepository(Tx_Messenger_Domain_Repository_MessageTemplateRepository $messageTemplateRepository) {
		$this->messageTemplateRepository = $messageTemplateRepository;
	}

	/**
	 * Initializes the controller before invoking an action method.
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 */
	public function initializeAction() {

		$this->listManager = Tx_Messenger_ListManager_Factory::getInstance();

		/** @var $validator Tx_Messenger_Validator_TableStructureValidator */
		$validator = t3lib_div::makeInstance('Tx_Messenger_Validator_TableStructureValidator');
		$validator->validate($this->listManager);
	}

	/**
	 * send a message
	 *
	 * @param int $messageUid
	 * @param string $recipients
	 * @return void
	 */
	public function sendMessageAction($messageUid, $recipients = '') {

	}

	/**
	 * send a message for testing
	 *
	 * @param int $messageUid
	 * @param string $testEmail
	 * @return void
	 */
	public function sendMessageTestAction($messageUid = 0, $testEmail = '') {

		/** @var $messageTemplate Tx_Messenger_Domain_Model_MessageTemplate */
		$messageTemplate = $this->messageTemplateRepository->findByUid($messageUid);
		if ($messageTemplate) {
			/** @var $message Tx_Messenger_Domain_Model_Message */
			$message = $this->objectManager->get('Tx_Messenger_Domain_Model_Message');

			// save email address as preference
			Tx_Messenger_Utility_BeUserPreference::set('messenger_testing_email', $testEmail);

		}

		return 'ok';
	}

	/**
	 * Save a setting for a BE User.
	 */
	public function set(){

	}
	/**
	 * Action list
	 *
	 * @return void
	 */
	public function indexAction() {

		$messageUid = Tx_Messenger_Utility_Configuration::get('messageUid');
		$messageTemplate = $this->messageTemplateRepository->findByUid($messageUid);

		$records = $this->listManager->getRecords();
		$this->view->assign('recipients', $records);
		$this->view->assign('message', $messageTemplate);
	}
}
?>