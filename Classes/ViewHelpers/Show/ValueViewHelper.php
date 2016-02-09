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

use Fab\Vidi\Tca\FieldType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Vidi\Tca\Tca;

/**
 * View helper which renders a value given by the context.
 */
class ValueViewHelper extends AbstractViewHelper {

	/**
	 * Return a value given by the context.
	 *
	 * @return string
	 */
	public function render() {
		$value = $this->templateVariableContainer->get('value');
		$fieldName = $this->templateVariableContainer->get('fieldName');

		if ($value instanceof ObjectStorage) {
			// Special case for file reference which is ok to be hardcoded.
			$names = array();
			foreach($value as $object) {
				if ($object instanceof FileReference) {
					$names[] = $object->getOriginalResource()->getName();
				}
			}
			$value = implode(', ', $names);
		} elseif ($this->templateVariableContainer->exists('dataType')) {
			$dataType = $this->templateVariableContainer->get('dataType');
			$fieldType = Tca::table($dataType)->field($fieldName)->getType();

			if ($fieldType === FieldType::RADIO || $fieldType === FieldType::SELECT) {
				$value = Tca::table($dataType)->field($fieldName)->getLabelForItem($value);
			} elseif ($fieldType === FieldType::TEXTAREA) {
				$value = nl2br($value);
			} elseif ($fieldType === FieldType::MULTISELECT) {
				$explodedValues = GeneralUtility::trimExplode(',', $value, TRUE);

				$labels = array();
				foreach ($explodedValues as $_value) {
					$label = Tca::table($dataType)->field($fieldName)->getLabelForItem($_value);
					if ($label) {
						$labels[] = $label;
					}
				}

				// Convert back array to a human understandable string.
				if (!empty($labels)) {
					$value = implode(', ', $labels);
				}
			}
		}
		return $value;
	}
}
