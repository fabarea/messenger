<?php

namespace Fab\Messenger\ViewHelpers\SentMessage;

use Closure;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class LabelViewHelper extends AbstractViewHelper
{
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ) {
        return self::getLanguageService()->sL(
            $GLOBALS['TCA'][$arguments['table']]['columns'][$arguments['field']]['label'],
        );
    }

    protected static function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('field', 'string', 'field', true);
        $this->registerArgument('table', 'string', 'table', true);
    }
}
