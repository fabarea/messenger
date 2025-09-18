<?php

namespace Fab\Messenger\Html2Text;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for access template engine.
 */
class TemplateEngine extends Enumeration
{
    final public const FLUID_ONLY = 'fluid';

    final public const FLUID_AND_MARKDOWN = 'both';
}
