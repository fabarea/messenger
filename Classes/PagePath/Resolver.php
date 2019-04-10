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
            $this->pageId = (int)$params['id'];
            $this->parameters = $params['parameters'];
        }
    }

    /**
     * Handles incoming trackback requests
     *
     * @return void
     */
    public function resolveUrl(): void
    {
        $myIp = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        $devIPMask = trim($GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);

        if ($myIp === $_SERVER['SERVER_ADDR'] || GeneralUtility::cmpIP($myIp, $devIPMask)) {
            header('Content-type: text/plain; charset=iso-8859-1');
            if ($this->pageId > 0) {

                $cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);

                /* @var $cObj \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
                $typoLinkConfiguration = array(
                    'parameter' => $this->pageId,
                    'useCacheHash' => $this->parameters !== '',
                );
                if ($this->parameters) {
                    $typoLinkConfiguration['additionalParams'] = $this->parameters;
                }
                $url = $cObj->typoLink_URL($typoLinkConfiguration);
                if ($url === '') {
                    $url = '/';
                }
                $parts = parse_url($url);
                if ($parts['host'] === '') {
                    $url = GeneralUtility::locationHeaderUrl($url);
                }
                echo $url;
                exit();
            }
        } else {
            echo 'Access denied!';
            header('HTTP/1.0 403 Access denied');
        }
    }

}
