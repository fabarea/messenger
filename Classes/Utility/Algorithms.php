<?php

namespace Fab\Messenger\Utility;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Random\RandomException;

/**
 * A utility class for various algorithms.
 */
class Algorithms
{
    /**
     * Generates a universally unique identifier (UUID) according to RFC 4122 v4.
     * The algorithm used here, might not be completely random.
     *
     * @return string The universally unique id
     * @throws RandomException
     * @author Unkown
     */
    public static function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
        );
    }
}
