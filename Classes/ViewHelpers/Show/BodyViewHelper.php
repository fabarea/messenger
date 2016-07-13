<?php
namespace Fab\Messenger\ViewHelpers\Show;

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
