<?php

namespace Fab\Messenger\Utility;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class for handling configuration of the extension
 */
class ConfigurationUtility implements SingletonInterface
{
    protected array $configuration = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('messenger');

        // Fill up configuration array with relevant values.
        foreach ($configuration as $key => $value) {
            $this->configuration[$key] = $value;
        }

        // Special case for "recipient_data_type"
        if (empty($this->configuration['recipient_data_type'])) {
            $this->configuration['recipient_data_type'] = 'fe_users';
        }
        
        // Ensure recipient_data_type is valid, fallback to fe_users if not
        if (!isset($GLOBALS['TCA'][$this->configuration['recipient_data_type']])) {
            $this->configuration['recipient_data_type'] = 'fe_users';
        }
    }

    public function get(string $key): mixed
    {
        return isset($this->configuration[$key]) ? trim((string) $this->configuration[$key]) : null;
    }

    /**
     * Returns a class instance.
     *
     * @return ConfigurationUtility
     */
    public static function getInstance(): ConfigurationUtility
    {
        return GeneralUtility::makeInstance(self::class);
    }

    public function set(string $key, mixed $value): void
    {
        $this->configuration[$key] = $value;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
