<?php
namespace Fab\Messenger\ViewHelpers\Show;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Fab\Messenger\Service\MessageStorage;

/**
 * View helper which return a key from the storage.
 */
class BodyViewHelper extends AbstractViewHelper
{

    /**
     * Return a key from the storage.
     *
     * @param string $identifier
     * @return string|NULL
     */
    public function render($identifier)
    {
        return MessageStorage::getInstance()->get($identifier);
    }
}
