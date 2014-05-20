<?php
namespace Vanilla\Messenger\ViewHelpers\Show;
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which tells whether the row of item should be rendered.
 */
class IsVisibleViewHelper extends AbstractViewHelper {

	/**
	 * Return whether the row of item should be rendered.
	 *
	 * @return bool
	 */
	public function render() {

		$fieldName = $this->templateVariableContainer->get('fieldName');
		$value = $this->templateVariableContainer->get('value');
		$dataType = '';
		if ($this->templateVariableContainer->exists('dataType')) {
			$dataType = $this->templateVariableContainer->get('dataType');
		}

		// Early return in case value is null, no need to show anything.
		if (is_null($value)) {
			return FALSE;
		}

		// Early return if an empty string is detected
		if (is_string($value) && strlen($value) == 0) {
			return FALSE;
		}

		$isVisible = TRUE;


		// Check whether the field name is not system.
		$displaySystemFields = $this->templateVariableContainer->get('displaySystemFields');
		if (FALSE === $displaySystemFields && $dataType) {
			$isVisible = TcaService::table($dataType)->field($fieldName)->isNotSystem();
		}

		// Check whether the field name is not to be excluded.
		$excludeFields = $this->templateVariableContainer->get('exclude');
		if ($isVisible) {
			$isVisible = !in_array($fieldName, $excludeFields);
		}
		return $isVisible;
	}
}
