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
class Tx_Messenger_Controller_SentMessageController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * sentMessageRepository
	 *
	 * @var Tx_Messenger_Domain_Repository_SentMessageRepository
	 */
	protected $sentMessageRepository;

	/**
	 * injectSentMessageRepository
	 *
	 * @param Tx_Messenger_Domain_Repository_SentMessageRepository $sentMessageRepository
	 * @return void
	 */
	public function injectSentMessageRepository(Tx_Messenger_Domain_Repository_SentMessageRepository $sentMessageRepository) {
		$this->sentMessageRepository = $sentMessageRepository;
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$sentMessages = $this->sentMessageRepository->findAll();
		$this->view->assign('sentMessages', $sentMessages);
	}

}
?>