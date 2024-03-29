<?php
namespace Fab\Messenger\Controller;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use RuntimeException;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * @deprecated should not be necessary anymore.
 * Controller which take the GET / POST arguments and generates an output given a Message Template.
 */
class MessageRendererController extends ActionController
{

    /**
     * @param string $registryIdentifier
     */
    public function renderAction($registryIdentifier): string
    {
        $registryEntry = $this->fetchRegistryEntry($registryIdentifier);

        if ($registryEntry === null) {
            throw new RuntimeException('Messenger: I could not find any valid entry from the registry.', 1_400_405_307);
        }

        /** @var StandaloneView $emailView */
        $emailView = GeneralUtility::makeInstance(StandaloneView::class);
        $emailView->setTemplateSource($registryEntry['content']);
        $emailView->assignMultiple($registryEntry['markers']);
        return $emailView->render();
    }

    /**
     * Fetch the entry of the registry Entry and clean up the registry afterwards.
     *
     * @param string $registryIdentifier
     */
    protected function fetchRegistryEntry($registryIdentifier): array
    {
        $registryEntry = $this->getRegistry()->get('Fab\Messenger', $registryIdentifier);
        $this->getRegistry()->remove('Fab\Messenger', $registryIdentifier);
        return $registryEntry;
    }

    /**
     * Returns an instance of the Frontend object.
     *
     * @return Registry
     */
    protected function getRegistry(): Registry
    {
        return GeneralUtility::makeInstance(Registry::class);
    }

}
