<?php

namespace Fab\Messenger\Service;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * A class dealing with MessageStorage.
 */
class MessageStorage implements SingletonInterface
{
    protected string $namespace = 'Fab\Messenger\\';

    public static function getInstance(): MessageStorage
    {
        return GeneralUtility::makeInstance(self::class);
    }

    public function get(string $key): mixed
    {
        $value = null;
        if ($this->isFrontendMode()) {
            $value = $this->getFrontendUser()->getKey('ses', $this->namespace . $key);
            $this->getFrontendUser()->setKey('ses', $this->namespace . $key, null); // unset variable
        }
        return $value;
    }

    protected function isFrontendMode(): bool
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }

    protected function getFrontendUser(): FrontendUserAuthentication
    {
        return $GLOBALS['TSFE']->fe_user;
    }

    public function set(string $key, mixed $value): static
    {
        if ($this->isFrontendMode()) {
            $this->getFrontendUser()->setKey('ses', $this->namespace . $key, $value);
        }
        return $this;
    }
}
