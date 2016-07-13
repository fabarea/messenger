<?php
namespace Fab\Messenger\Validator;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\SingletonInterface;
use Fab\Messenger\Exception\InvalidEmailFormatException;

/**
 * Validate Email in the context of SwiftMailer
 */
class EmailValidator implements SingletonInterface
{

    /**
     * Validate emails to be used in the SwiftMailer framework
     *
     * @throws InvalidEmailFormatException
     * @param $emails
     * @return boolean
     */
    public function validate($emails)
    {
        foreach ($emails as $email => $name) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = sprintf('Email provided is not valid, given value "%s"', $email);
                throw new InvalidEmailFormatException($message, 1350297165);
            }
            if (strlen($name) <= 0) {
                $message = sprintf('Name should not be empty, given value "%s"', $name);
                throw new InvalidEmailFormatException($message, 1350297170);
            }
        }
        return TRUE;
    }
}
