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
class Tx_Messenger_Controller_ListManagerController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * Action list
	 *
	 * @return void
	 */
	public function listAction() {
		$this->view->assign('listManagers', Tx_Messenger_ListManager_Registry::getInstance()->get());
	}

	/**
	 * Action list
	 *
	 * @param string $listManager
	 * @return void
	 */
	public function saveAction($listManager) {
		// save email address as preference
		Tx_Messenger_Utility_BeUserPreference::set('messenger_list_manager', $listManager);
		$this->redirect('index', 'Backend');
	}
}
?>