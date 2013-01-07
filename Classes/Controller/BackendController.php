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
	 * userRepository
	 *
	 * @var Tx_Extbase_Persistence_Repository
	 */
	protected $userRepository;

	/**
	 * injectUserRepository
	 *
	 * @param Tx_Extbase_Persistence_Repository $userRepository
	 * @return void
	 */
	public function injectUserRepository(Tx_Extbase_Persistence_Repository $userRepository) {
		$this->userRepository = $userRepository;
	}

	/**
	 * Initializes the controller before invoking an action method.
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 */
	public function initializeAction() {
		$className = $this->configuration['userRepository'];

		if (class_exists($className)) {
			/** @var $userRepository Tx_Extbase_Persistence_Repository */
			$userRepository = t3lib_div::makeInstance($className);

			$this->injectUserRepository($userRepository);
		}
	}

	/**
	 * Action list
	 *
	 * @return void
	 */
	public function indexAction() {

//
//		$uid = $this->configuration['messageUid'];
//		$message = $this->messageRepository->findByUid($uid);
//
//		$users = $this->userRepository->findAll();
//
//		// @todo default filter
//
//		$this->view->assign('users', $users);
//		$this->view->assign('message', $message);

	}

}
?>