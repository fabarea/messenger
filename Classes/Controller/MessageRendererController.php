<?php
namespace Vanilla\Messenger\Controller;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Vanilla\Messenger\Domain\Model\MessageTemplate;

/**
 * Controller which take the GET / POST arguments and generates an output given a Message Template.
 */
class MessageRendererController extends ActionController {

	/**
	 * @param string $registryIdentifier
	 * @throws \RuntimeException
	 * @return string
	 */
	public function renderAction($registryIdentifier) {

		$registryEntry = $this->fetchRegistryEntry($registryIdentifier);

		if (is_null($registryEntry)) {
			throw new \RuntimeException('Messenger: I could not find any valid entry from the registry.', 1400405307);
		}

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
		$emailView = $this->objectManager->get('TYPO3\CMS\Fluid\View\StandaloneView');
		$emailView->setTemplateSource($registryEntry['content']);
		$emailView->assignMultiple($registryEntry['markers']);
		return $emailView->render();
	}

	/**
	 * Fetch the entry of the registry Entry and clean up the registry afterwards.
	 *
	 * @param string $registryIdentifier
	 * @return \TYPO3\CMS\Core\Registry
	 */
	protected function fetchRegistryEntry($registryIdentifier) {
		$registryEntry = $this->getRegistry()->get('Vanilla\Messenger', $registryIdentifier);
		$this->getRegistry()->remove('Vanilla\Messenger', $registryIdentifier);
		return $registryEntry;
	}

	/**
	 * Returns an instance of the Frontend object.
	 *
	 * @return \TYPO3\CMS\Core\Registry
	 */
	protected function getRegistry() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');
	}

}
