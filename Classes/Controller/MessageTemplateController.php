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
class Tx_Messenger_Controller_MessageTemplateController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Messenger_Domain_Repository_MessageTemplateRepository
	 * @inject
	 */
	protected $messageTemplateRepository;

	/**
	 * @return void
	 */
	public function listAction() {
		$messageTemplates = $this->messageTemplateRepository->findAll();
		$this->view->assign('messageTemplates', $messageTemplates);
	}

	/**
	 * @param string $messageTemplate
	 * @return void
	 */
	public function saveAction($messageTemplate) {
		// save email address as preference
		Tx_Messenger_Utility_BeUserPreference::set('messenger_message_template', $messageTemplate);
		$this->redirect('index', 'Backend');
	}
}
?>