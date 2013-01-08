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
	 * @var Tx_Messenger_Interface_TableStructureInterface
	 */
	protected $tableStructure;

	/**
	 * Initializes the controller before invoking an action method.
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 */
	public function initializeAction() {

		$this->tableStructure = Tx_Messenger_TableStructure_Factory::getInstance();

		/** @var $validator Tx_Messenger_Validator_TableStructureValidator */
		$validator = t3lib_div::makeInstance('Tx_Messenger_Validator_TableStructureValidator');

		$validator->validate($this->tableStructure);
	}

	/**
	 * Action list
	 *
	 * @return void
	 */
	public function indexAction() {

//		$uid = $this->configuration['messageUid'];
//		$message = $this->messageRepository->findByUid($uid);
//		$users = $this->userRepository->findAll();
		$users = $this->tableStructure->getUsers();
		$this->view->assign('users', $users);

//		$this->view->assign('message', $message);
	}
}
?>