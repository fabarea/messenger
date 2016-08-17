<?php
namespace Fab\Messenger\PagePath;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class create frontend page address from the page id value and parameters.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 */
class Resolver
{

    /**
     * @var int
     */
    protected $pageId;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * Initializes the instance of this class.
     */
    public function __construct()
    {
        $params = unserialize(base64_decode(GeneralUtility::_GP('data')));
        if (is_array($params)) {
            $this->pageId = $params['id'];
            $this->parameters = $params['parameters'];
        }
    }

    /**
     * Handles incoming trackback requests
     *
     * @return    void
     */
    public function main()
    {
        header('Content-type: text/plain; charset=iso-8859-1');
        if ($this->pageId) {
            $this->createTSFE();

            $cObj = GeneralUtility::makeInstance('tslib_cObj');

            /* @var $cObj \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
            $typoLinkConfiguration = array(
                'parameter' => $this->pageId,
                'useCacheHash' => $this->parameters != '',
            );
            if ($this->parameters) {
                $typoLinkConfiguration['additionalParams'] = $this->parameters;
            }
            $url = $cObj->typoLink_URL($typoLinkConfiguration);
            if ($url == '') {
                $url = '/';
            }
            $parts = parse_url($url);
            if ($parts['host'] == '') {
                $url = GeneralUtility::locationHeaderUrl($url);
            }
            echo $url;
        }
    }

    /**
     * Initializes TSFE. This is necessary to have proper environment for typoLink.
     *
     * @return    void
     */
    protected function createTSFE()
    {

        $this->getFrontendObject()->connectToDB();
        $this->getFrontendObject()->initFEuser();
        $this->getFrontendObject()->determineId();
        $this->getFrontendObject()->initTemplate();
        $this->getFrontendObject()->getConfigArray();

        \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
    }

    /**
     * Returns an instance of the Frontend object.
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getFrontendObject()
    {
        if (is_null($GLOBALS['TSFE'])) {
            $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], $this->pageId, 0);
        }
        return $GLOBALS['TSFE'];
    }
}

$myIp = GeneralUtility::getIndpEnv('REMOTE_ADDR');
$devIPMask = trim($GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);

if ($myIp == $_SERVER['SERVER_ADDR'] || GeneralUtility::cmpIP($myIp, $devIPMask)) {
    $resolver = GeneralUtility::makeInstance('Fab\Messenger\PagePath\Resolver');

    /* @var $resolver \Fab\Messenger\PagePath\Resolver */
    $resolver->main();
} else {
    echo 'Access denied!';
    header('HTTP/1.0 403 Access denied');
}
