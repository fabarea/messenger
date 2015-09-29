<?php
namespace Fab\Messenger\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Fab\Messenger\Domain\Model\MessageTemplate;

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
		$registryEntry = $this->getRegistry()->get('Fab\Messenger', $registryIdentifier);
		$this->getRegistry()->remove('Fab\Messenger', $registryIdentifier);
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
