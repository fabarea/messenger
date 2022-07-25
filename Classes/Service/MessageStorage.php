<?php
namespace Fab\Messenger\Service;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use InvalidArgumentException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * A class dealing with MessageStorage.
 */
class MessageStorage implements SingletonInterface
{

    /**
     * @var string
     */
    protected $namespace = 'Fab\Messenger\\';

    /**
     * Returns a class instance
     *
     * @return MessageStorage
     * @throws InvalidArgumentException
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Get a stored value for this run time.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $value = NULL;
        if ($this->isFrontendMode()) {
            $value = $this->getFrontendUser()->getKey('ses', $this->namespace . $key);
            $this->getFrontendUser()->setKey('ses', $this->namespace . $key, NULL); // unset variable
        }
        return $value;
    }

    /**
     * Store a value for this run time.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value)
    {
        if ($this->isFrontendMode()) {
            $this->getFrontendUser()->setKey('ses', $this->namespace . $key, $value);
        }
        return $this;
    }

    /**
     * Returns an instance of the current Frontend User.
     *
     * @return FrontendUserAuthentication
     */
    protected function getFrontendUser()
    {
        return $GLOBALS['TSFE']->fe_user;
    }

    /**
     * Returns whether the current mode is Frontend
     *
     * @return bool
     */
    protected function isFrontendMode()
    {
        return TYPO3_MODE === 'FE';
    }
}
