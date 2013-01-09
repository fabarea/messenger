<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Fabien Udriot <fudriot@cobweb.ch>, Cobweb
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
 *
 * @package messenger
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Messenger_Utility_Marker {

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
		$this->view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
		$this->contentObject = t3lib_div::makeInstance('tslib_cObj');
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
			$config['parseFunc.'] = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
			$config['value'] = $input;
			$input = $this->contentObject->TEXT($config);
		}

		$this->view->setTemplateSource($input);
		$this->view->assignMultiple($markers);
		return $this->view->render();
	}
}
?>