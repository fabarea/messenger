<?php

namespace Fab\Messenger\ViewHelpers\Message;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Exception\InvalidEmailFormatException;
use Fab\Messenger\Redirect\RedirectService;
use Fab\Messenger\Service\SenderProvider;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper to some honey pot field.
 */
class DevelopmentViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     * @api
     */
    protected $escapeOutput = false;

    /**
     * @throws InvalidEmailFormatException
     */
    public function render(): string
    {
        $redirectTo = $this->getRedirectService()->getRedirections();
        $output = '';

        // Means we want to redirect email.
        if ($redirectTo) {
            $output = sprintf(
                "<pre style='clear: both'>%s CONTEXT<br /> %s %s</pre>",
                strtoupper((string)Environment::getContext()),
                '<br />- All emails will be redirected to ' . implode(', ', array_keys($redirectTo)) . '.',
                SenderProvider::getInstance()->getPossibleSenders()
                    ? ''
                    : '<br/>- ATTENTION! No sender could be found. This will be a problem when sending emails.',
            );
        }

        return $output;
    }

    /**
     * @return RedirectService
     */
    public function getRedirectService(): RedirectService
    {
        return GeneralUtility::makeInstance(RedirectService::class);
    }
}
