<?php

namespace Fab\Messenger\ViewHelpers\Show;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Service\MessageStorage;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which return a key from the storage.
 */
class BodyViewHelper extends AbstractViewHelper
{
    /**
     * Initialize arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('identifier', 'string', 'The identifier', true);
    }

    public function render(): ?string
    {
        $identifier = $this->arguments['identifier'];
        return MessageStorage::getInstance()->get($identifier);
    }
}
