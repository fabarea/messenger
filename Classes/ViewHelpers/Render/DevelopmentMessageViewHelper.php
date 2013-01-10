<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * View helper that a render a development message
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  messenger
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class Tx_Messenger_ViewHelpers_Render_DevelopmentMessageViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Render a message in development context.
	 *
	 * Note: this view helper could be programmed in a more generic way by translating a string given a number of input value arguments.
	 *
	 * @return string
	 */
	public function render() {
		$value = '';
		if (Tx_Messenger_Utility_Context::getInstance()->isNotProduction()) {
			$emails = Tx_Messenger_Utility_Configuration::getInstance()->get('developmentEmails');
			$label = sprintf(Tx_Extbase_Utility_Localization::translate('development_mode', 'messenger'), $emails);
			$value = sprintf('<span class="label">%s</span>', $label);
		}
		return $value;
	}
}

?>