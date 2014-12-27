<?php
namespace Vanilla\Messenger\PagePath;

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

/**
 * This class create frontend page address from the page id value and parameters.
 *
 * @author    Dmitry Dulepov <dmitry@typo3.org>
 */
class Resolver {

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
	public function __construct() {
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
	public function main() {
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
	protected function createTSFE() {

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
	protected function getFrontendObject() {
		if (is_null($GLOBALS['TSFE'])) {
			$GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], $this->pageId, 0);
		}
		return $GLOBALS['TSFE'];
	}
}

if (GeneralUtility::getIndpEnv('REMOTE_ADDR') != $_SERVER['SERVER_ADDR']) {
	header('HTTP/1.0 403 Access denied');
	// Empty output!!!
} else {
	$resolver = GeneralUtility::makeInstance('Vanilla\Messenger\PagePath\Resolver');

	/* @var $resolver \Vanilla\Messenger\PagePath\Resolver */
	$resolver->main();
}
