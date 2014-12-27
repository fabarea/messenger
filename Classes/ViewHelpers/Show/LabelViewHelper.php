<?php
namespace Vanilla\Messenger\ViewHelpers\Show;

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
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which renders a label given by the fieldName in the context.
 */
class LabelViewHelper extends AbstractViewHelper {

	/**
	 * Return a label given by the fieldName in the context.
	 *
	 * @return string
	 */
	public function render() {
		$label = '';
		$fieldName = $this->templateVariableContainer->get('fieldName');
		if ($this->templateVariableContainer->exists('dataType')) {
			$dataType = $this->templateVariableContainer->get('dataType');
			$label = TcaService::table($dataType)->field($fieldName)->getLabel();
		}
		return $label;
	}
}
