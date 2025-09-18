<?php

namespace Fab\Messenger\ContentRenderer;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Model\MessageTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * This class is for rendering content in the context of the Frontend.
 */
class FrontendRenderer implements ContentRendererInterface
{
    /**
     * Constructor
     *
     * @param MessageTemplate|null $messageTemplate
     */
    public function __construct(protected ?MessageTemplate $messageTemplate = null) {}

    /**
     * Render content in the context of the Frontend.
     *
     * @param string $content
     */
    public function render($content, array $markers): string
    {
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);

        // Handle double {{ }} to be interpreted as HTML
        $content = preg_replace('/{{(.*)}}/', '<f:format.raw>{$1}</f:format.raw>', $content);

        $view->setTemplateSource($content);

        // If a template file was defined, set its path, so that layouts and partials can be used
        // NOTE: they have to be located in sub-folders called "Layouts" and "Partials" relative
        // to the folder where the template is stored.
        if ($this->messageTemplate && $this->messageTemplate->getSourceFile()) {
            $sourceFile = $this->messageTemplate->getSourceFile();
            if (!empty($sourceFile)) {
                $sourceFileNameAndPath = GeneralUtility::getFileAbsFileName($sourceFile);
                $view->setTemplatePathAndFilename($sourceFileNameAndPath);
            }
        }

        $view->assignMultiple($markers);
        return trim((string)$view->render());

        // Check if tidy is required for email.
        //$content = trim($view->render());
        //$content = array_map('trim', explode("\n", $content));
        //return implode("\n", $content);
    }
}
