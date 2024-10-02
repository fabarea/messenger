<?php

namespace Fab\Messenger\Domain\Model;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Message Layout representation
 */
class MessageLayout extends AbstractEntity
{
    protected mixed $identifier;

    protected mixed $content;

    /**
     * Constructor
     */
    public function __construct(array $data = [])
    {
        $this->identifier = !empty($data['identifier']) ? $data['identifier'] : '';
        $this->content = !empty($data['content']) ? $data['content'] : '';
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
