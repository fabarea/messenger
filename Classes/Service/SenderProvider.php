<?php
namespace Fab\Messenger\Service;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SenderProvider
 */
class SenderProvider implements SingletonInterface
{

    /**
     * Returns a class instance
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * @return array
     */
    public function getFormattedPossibleSenders()
    {
        $senders = $this->getPossibleSenders();

        $formattedSenders = [];
        foreach ($senders as $key => $sender) {
            $email = key($sender);
            if ($email !== $sender[$email]) {
                $formattedSenders[$key] = sprintf('%s <%s>', $sender[$email], $email);
            } else {
                $formattedSenders[$key] = $sender[$email];
            }
        }

        return $formattedSenders;
    }

    /**
     * @return array
     */
    public function getPossibleSenders()
    {
        $senders = $this->getFromTSConfig();
        if ($this->getFromPhpSettings()) {
            $senders['php'] = $this->getFromPhpSettings();
        }

        if ($this->getMe()) {
            $senders['me'] = $this->getMe();
        }

        return $senders;
    }

    /**
     * @return array
     */
    protected function getFromPhpSettings()
    {
        $phpSettings = [];
        if ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']) {
            $email = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
            $name = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
                ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];


            $phpSettings = [$email => $name];
        }
        return $phpSettings;
    }

    /**
     * @return array
     */
    protected function getFromTSConfig()
    {
        $senders = [];
        $tsSenders = $this->getBackendUser()->getTSConfigProp('options.messenger.senders');
        if (is_array($tsSenders)) {

            foreach ($tsSenders as $key => $sender) {

                $senders[$key] = [$sender['email'] => $sender['name']];
            }
        }

        return $senders;
    }

    /**
     * @return array
     */
    protected function getMe()
    {
        $me = [];
        if ($this->getBackendUser()->user['email']) {
            $email = $this->getBackendUser()->user['email'];
            $name = $this->getBackendUser()->user['realName']
                ?: $this->getBackendUser()->user['email'];


            $me = [$email => $name];
        }
        return $me;
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }


}
