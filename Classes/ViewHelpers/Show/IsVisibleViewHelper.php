<?php
namespace Fab\Messenger\ViewHelpers\Show;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\TcaService;

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
