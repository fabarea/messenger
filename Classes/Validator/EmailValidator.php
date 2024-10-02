<?php

namespace Fab\Messenger\Validator;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Exception\InvalidEmailFormatException;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Validate Email in the context of SwiftMailer
 */
class EmailValidator implements SingletonInterface
{
    /**
     * Validate emails to be used in the SwiftMailer framework
     *
     * @param $emails
     * @return boolean
     * @throws InvalidEmailFormatException
     */
    public function validate($emails): bool
    {
        foreach ($emails as $email => $name) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = sprintf('Email provided is not valid, given value "%s"', $email);
                throw new InvalidEmailFormatException($message, 1_350_297_165);
            }
            if (strlen((string) $name) <= 0) {
                $message = sprintf('Name should not be empty, given value "%s"', $name);
                throw new InvalidEmailFormatException($message, 1_350_297_170);
            }
        }
        return true;
    }
}
