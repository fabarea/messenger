<?php
namespace Fab\Messenger\ContentRenderer;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\PagePath\PagePath;
use Fab\Messenger\Utility\Algorithms;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is for rendering content in the context of the Backend.
 */
class BackendRenderer implements ContentRendererInterface
{

    /**
     * Render content in the context of the Backend.
     * This is required in order to correctly resolve the View Helpers for Fluid in the context of the Backend.
     *
     * @param string $content
     * @param array $markers
     * @return string
     * @throws \UnexpectedValueException
     */
    public function render($content, array $markers)
    {
        $registryIdentifier = Algorithms::generateUUID();
        $registryEntry = array(
            'content' => $content,
            'markers' => $markers,
        );

        // Register data to be fetch in the Frontend Context
        $this->getRegistry()->set('Fab\Messenger', $registryIdentifier, $registryEntry);

        // Prepare the URL for the Crawler.
        $rootPageUid = $this->getConfigurationUtility()->get('rootPageUid');
        $parameters['type'] = 1370537883;
        $parameters['tx_messenger_pi1[registryIdentifier]'] = $registryIdentifier;
        $url = PagePath::getUrl($rootPageUid, $parameters);

        if (!$url) {
            $message = 'ERROR in Messenger!' . chr(10);
            $message .= sprintf(
                'As a first measure, add this IP "%s" to your "devIPmask" settings. Debug me if the problem persists...%s',
                GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                chr(10)
            );
            die($message);
        }

        // Send TYPO3 cookies as this may affect path generation
        $headers = array(
            'Cookie: fe_typo_user=' . $_COOKIE['fe_typo_user']
        );

        // Fetch content
        $formattedContent = GeneralUtility::getURL($url, false, $headers);
        return trim($formattedContent);
    }

    /**
     * @return \Fab\Messenger\Utility\ConfigurationUtility
     */
    public function getConfigurationUtility()
    {
        return GeneralUtility::makeInstance('Fab\Messenger\Utility\ConfigurationUtility');
    }

    /**
     * @return \TYPO3\CMS\Core\Registry
     */
    protected function getRegistry()
    {
        return GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');
    }

}
