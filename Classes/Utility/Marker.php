<?php
namespace Vanilla\Messenger\Utility;
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

/**
 *
 */
class Marker {

	/**
	 * @var Tx_Fluid_View_StandaloneView
	 */
	protected $view;

	/**
	 * @var tslib_cObj
	 */
	protected $contentObject;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Fluid_View_StandaloneView');
		$this->contentObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');
	}

	/**
	 * Substitute markers
	 *
	 * @param string $input
	 * @param array $markers
	 * @param string $format can be format
	 * @return string the formatted string
	 */
	public function substitute($input, array $markers, $format = 'text/html') {

		if ($format == 'text/html') {

			// Rather use the format view helper
			$input = sprintf('<f:format.html parseFuncTSPath="lib.parseFunc_Mail">%s</f:format.html>', $input);

			#$config['parseFunc.'] = array();
			#if (TYPO3_MODE == 'BE') {
			#	$config['parseFunc.'] = $this->getRteConfiguration();
			#} elseif (TYPO3_MODE == 'FE') {
			#	$config['parseFunc.'] = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
			#
			#}
			#$config['value'] = $input;
			#$input = $this->contentObject->TEXT($config);
		}

		$this->view->setTemplateSource($input);
		$this->view->assignMultiple($markers);
		return $this->view->render();
	}

	/**
	 * Load the TypoScript in the Backend and returns the RTE configuration.
	 *
	 * @return array
	 */
	protected function getRteConfiguration() {
		$pageUid = \Vanilla\Messenger\Utility\Configuration::getInstance()->get('rootPageUid');
		$sysPageObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_pageSelect');
		$rootLine = $sysPageObj->getRootLine($pageUid);
		$TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_tsparser_ext');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($rootLine);
		$TSObj->generateConfig();
		return $TSObj->setup['lib.']['parseFunc_RTE.'];
	}

}
?>