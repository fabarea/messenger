<?php
namespace TYPO3\CMS\Messenger\Controller;
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
use TYPO3\CMS\Messenger\Utility\BeUserPreference;

/**
 *
 */
class MessageTemplateController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\CMS\Messenger\Domain\Repository\MessageTemplateRepository
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
	 * @param int $messageTemplate
	 * @return void
	 */
	public function saveAction($messageTemplate) {

		// save email address as preference
		BeUserPreference::set('messenger_message_template', $messageTemplate);
		$this->redirect('index', 'Backend');
	}
}
?>