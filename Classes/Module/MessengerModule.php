<?php

namespace Fab\Messenger\Module;

/*
 * This file is part of the Fab/Media project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class MessengerModule
 */
class MessengerModule implements SingletonInterface
{
    /**
     * @var string
     */
    final public const SIGNATURE = 'user_MessengerM1';

    /**
     * @var string
     */
    final public const PARAMETER_PREFIX = 'tx_messenger_user_messengerm1';

    /**
     * @return string
     */
    public static function getSignature(): string
    {
        return self::SIGNATURE;
    }

    /**
     * @return string
     */
    public static function getParameterPrefix(): string
    {
        return self::PARAMETER_PREFIX;
    }
}
