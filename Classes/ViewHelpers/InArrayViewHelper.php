<?php

namespace Fab\Messenger\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class InArrayViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('haystack', 'string', 'haystack', true);
        $this->registerArgument('needle', 'string', 'need', true);
    }

    protected static function evaluateCondition($arguments = null): bool
    {
        return in_array($arguments['needle'], $arguments['haystack'], true);
    }
}
