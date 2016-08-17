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
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * A class for handling configuration of the extension
 */
class ConfigurationUtility implements SingletonInterface
{

    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * Returns a class instance.
     *
     * @return \Fab\Messenger\Utility\ConfigurationUtility
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance('Fab\Messenger\Utility\ConfigurationUtility');
    }

    /**
     * Constructor
     *
     * @return \Fab\Messenger\Utility\ConfigurationUtility
     */
    public function __construct()
    {

        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->getObjectManager()->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
        $configuration = $configurationUtility->getCurrentConfiguration('messenger');

        // Fill up configuration array with relevant values.
        foreach ($configuration as $key => $data) {
            $this->configuration[$key] = $data['value'];
        }
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
    }

    /**
     * Returns a setting key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->configuration[$key]) ? trim($this->configuration[$key]) : NULL;
    }

    /**
     * Set a setting key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->configuration[$key] = $value;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

}
