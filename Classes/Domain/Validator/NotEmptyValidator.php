<?php

namespace Fab\Messenger\Domain\Validator;

/*
 * This file is part of the Fab/Mailing project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validate the honey pot.
 */
class NotEmptyValidator extends AbstractValidator
{
    public function isValid(mixed $value): void
    {
        if (!trim($value)) {
            $this->addError('Empty field', 1_468_509_656);
        }
    }
}
