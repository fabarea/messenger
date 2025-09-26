<?php

namespace Fab\Messenger\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class InArrayViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('haystack', 'array', 'haystack', true);
        $this->registerArgument('needle', 'string', 'needle', true);
    }

    protected static function evaluateCondition($arguments = null): bool
    {

        if (!is_array($arguments['haystack']) || !array_key_exists('needle', $arguments)) {
            return false;
        }
        return in_array($arguments['needle'], $arguments['haystack'], true);
    }
}
