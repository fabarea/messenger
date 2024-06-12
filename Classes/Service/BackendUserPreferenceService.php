<?php

namespace Fab\Messenger\Service;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendUserPreferenceService
{
    public static function getInstance(): BackendUserPreferenceService
    {
        return GeneralUtility::makeInstance(\Fab\Messenger\Service\BackendUserPreferenceService::class);
    }

    public function get(string $key): mixed
    {
        $result = null;
        if ($this->getBackendUser() && !empty($this->getBackendUser()->uc[$key])) {
            $result = $this->getBackendUser()->uc[$key];
        }
        return $result;
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->getBackendUser()) {
            $this->getBackendUser()->uc[$key] = $value;
            $this->getBackendUser()->writeUC();
        }
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
