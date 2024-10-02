<?php

namespace Fab\Messenger\ViewHelpers\Show;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Utility\FieldType;
use Fab\Messenger\Utility\TcaFieldsUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which renders a value given by the context.
 */
class ValueViewHelper extends AbstractViewHelper
{
    public function render(): string
    {
        $value = $this->templateVariableContainer->get('value');
        $fieldName = $this->templateVariableContainer->get('fieldName');

        if ($value instanceof ObjectStorage) {
            // Special case for file reference which is ok to be hardcoded.
            $names = [];
            foreach ($value as $object) {
                if ($object instanceof FileReference) {
                    $names[] = $object->getOriginalResource()->getName();
                }
            }
            $value = implode(', ', $names);
        } elseif ($this->templateVariableContainer->exists('dataType')) {
            $dataType = $this->templateVariableContainer->get('dataType');
            $fieldType = TcaFieldsUtility::getFields($dataType);

            if ($fieldType === FieldType::RADIO || $fieldType === FieldType::SELECT) {
                $value = TcaFieldsUtility::getFields($dataType);
            } elseif ($fieldType === FieldType::TEXTAREA) {
                $value = nl2br((string) $value);
            } elseif ($fieldType === FieldType::MULTISELECT) {
                $explodedValues = GeneralUtility::trimExplode(',', $value, true);

                $labels = [];
                foreach ($explodedValues as $_value) {
                    $label = TcaFieldsUtility::getFields($dataType);
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
