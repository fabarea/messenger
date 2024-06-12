<?php
namespace Fab\Messenger\ViewHelpers\SentMessage;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class LabelViewHelper extends AbstractViewHelper
{
    protected static string $tableName = 'tx_messenger_domain_model_sentmessage';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('field', 'string', 'field', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ) {
        return self::getLanguageService()->sL(
            $GLOBALS['TCA'][self::$tableName]['columns'][$arguments['field']]['label'],
        );
    }

    protected static function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
