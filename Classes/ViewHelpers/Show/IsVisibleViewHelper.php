<?php

namespace Fab\Messenger\ViewHelpers\Show;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Countable;
use Fab\Messenger\Utility\TcaFieldsUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which tells whether the row of item should be rendered.
 */
class IsVisibleViewHelper extends AbstractViewHelper
{
    /**
     * Return whether the row of item should be rendered.
     *
     * @return bool
     */
    public function render(): bool
    {
        $fieldName = $this->templateVariableContainer->get('fieldName');
        $value = $this->templateVariableContainer->get('value');
        $dataType = '';
        if ($this->templateVariableContainer->exists('dataType')) {
            $dataType = $this->templateVariableContainer->get('dataType');
        }

        // Early return in case value is null, no need to show anything.
        if (is_null($value)) {
            return false;
        }

        // Early return if an empty string is detected
        if (is_string($value) && strlen($value) == 0) {
            return false;
        }

        // Early return if the value is countable and contains nothing
        if ($value instanceof \Countable && $value->count() === 0) {
            return false;
        }

        $isVisible = true;

        // Check whether the field name is not system.
        $displaySystemFields = $this->templateVariableContainer->get('displaySystemFields');
        if ($displaySystemFields === false && $dataType) {
            $isVisible = TcaFieldsUtility::getFields($dataType);
        }

        // Check whether the field name is not to be excluded.
        $excludeFields = $this->templateVariableContainer->get('exclude');
        if ($isVisible) {
            $isVisible = !in_array($fieldName, $excludeFields);
        }
        return $isVisible;
    }
}
