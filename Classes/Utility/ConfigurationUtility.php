<?php
namespace Fab\Messenger\Utility;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class for handling configuration of the extension
 */
class ConfigurationUtility implements SingletonInterface
{

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * Returns a class instance.
     *
     * @return \Fab\Messenger\Utility\ConfigurationUtility
     */
    public static function getInstance(): \Fab\Messenger\Utility\ConfigurationUtility
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get('messenger');

        // Fill up configuration array with relevant values.
        foreach ($configuration as $key => $value) {
            $this->configuration[$key] = $value;
        }

        // Special case for "recipient_data_type"
        if (empty($this->configuration['recipient_data_type'])) {
            $this->configuration['recipient_data_type'] = 'fe_users';
        }
    }

    /**
     * Returns a setting key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->configuration[$key]) ? trim((string) $this->configuration[$key]) : null;
    }

    /**
     * Set a setting key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value): void
    {
        $this->configuration[$key] = $value;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

}
