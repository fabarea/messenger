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

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $content;

    /**
     * Constructor
     */
    public function __construct(array $data = [])
    {
        $this->identifier = !empty($data['identifier']) ? $data['identifier'] : '';
        $this->content = !empty($data['content']) ? $data['content'] : '';
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

}
