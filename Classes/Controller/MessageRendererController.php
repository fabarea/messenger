<?php
namespace TYPO3\CMS\Messenger\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Messenger\Domain\Model\MessageTemplate;

/**
 * Controller which take the GET / POST arguments and generates an output given a Message Template.
 */
class MessageRendererController extends ActionController {

	/**
	 * Initialize object
	 */
	public function initializeAction() {

		// @todo add IP address check. This controller is called by a crawler and is not meant to be called from the "outside"
	}

	/**
	 * @param \TYPO3\CMS\Messenger\Domain\Model\MessageTemplate $messageTemplate
	 * @param array $markers
	 * @return string
	 */
	public function renderAction(MessageTemplate $messageTemplate, $markers = array()) {

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
		$emailView = $this->objectManager->get('TYPO3\CMS\Fluid\View\StandaloneView');
		$emailView->setTemplateSource($messageTemplate->getBody());
		$emailView->assignMultiple($markers);
		return $emailView->render();
	}
}
?>