<?php

namespace Fab\Messenger\Utility;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Standard functions available for the TYPO3 backend.
 */
class BackendUtility
{
    public static function getModuleUrl(string $moduleName, array $urlParameters = []): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        try {
            $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
        } catch (RouteNotFoundException) {
            $uri = $uriBuilder->buildUriFromRoutePath($moduleName, $urlParameters);
        }
        return (string) $uri;
    }
}
