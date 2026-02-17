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
        $haystack = $arguments['haystack'] ?? [];
        $needle = $arguments['needle'] ?? '';

        if (!is_array($haystack) || $needle === '') {
            return false;
        }

        return in_array($needle, $haystack, false);
    }
    
    /**
     * Override render to ensure arguments are properly passed
     */
    public function render(): string
    {
        if (static::evaluateCondition($this->arguments)) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }
}
