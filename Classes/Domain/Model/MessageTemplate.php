<?php

namespace Fab\Messenger\Domain\Model;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Html2Text\TemplateEngine;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Message Template representation
 */
class MessageTemplate extends AbstractEntity
{
    final public const TYPE_TEXT = 1;
    final public const TYPE_PAGE = 2;
    final public const TYPE_FILE = 3;

    /**
     * @var string
     */
    protected mixed $qualifier;

    /**
     * @var int
     */
    protected int $type;

    /**
     * @var int
     */
    protected mixed $sourcePage;

    /**
     * @var string
     */
    protected mixed $sourceFile;

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator
     */
    protected mixed $subject;

    /**
     * @var string
     */
    protected mixed $body;

    /**
     * @var string
     */
    protected string $templateEngine;

    /**
     * Constructor
     */
    public function __construct(array $data = [])
    {
        $this->qualifier = empty($data['qualifier']) ? '' : $data['qualifier'];
        $this->type = empty($data['type']) ? 0 : (int)$data['type'];
        $this->sourcePage = empty($data['source_page']) ? 0 : $data['source_page'];
        $this->sourceFile = empty($data['source_file']) ? '' : $data['source_file'];
        $this->subject = empty($data['subject']) ? '' : $data['subject'];
        $this->body = empty($data['body']) ? '' : $data['body'];
    }

    /**
     * Returns the subject
     *
     * @return string $subject
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Sets the subject
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Returns the body according to the type of the message template.
     *
     * @throws \RuntimeException
     * @return string $body
     */
    public function getBody(): string
    {

        if ($this->type === self::TYPE_PAGE) {
            // @todo use $crawler to fetch body content of page
            throw new \RuntimeException('Messenger: not implemented', 1_400_517_075);

        }
        if ($this->type === self::TYPE_FILE) {
            $file = GeneralUtility::getFileAbsFileName($this->sourceFile);
            if (!is_file($file)) {
                $message = sprintf('Messenger: I could not found file "%s"', $file);
                throw new \RuntimeException($message, 1_400_517_074);
            }
            $this->body = file_get_contents($file);
        }

        return $this->body;
    }

    /**
     * Sets the body
     *
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string $qualifier
     */
    public function getQualifier(): string
    {
        return $this->qualifier;
    }

    /**
     * @param string $qualifier
     */
    public function setQualifier(string $qualifier): void
    {
        $this->qualifier = $qualifier;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getSourcePage(): int
    {
        return $this->sourcePage;
    }

    /**
     * @param int $sourcePage
     * @return $this
     */
    public function setSourcePage(int $sourcePage): static
    {
        $this->sourcePage = $sourcePage;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceFile(): string
    {
        return $this->sourceFile;
    }

    /**
     * @param string $sourceFile
     * @return $this
     */
    public function setSourceFile(string $sourceFile): static
    {
        $this->sourceFile = $sourceFile;
        return $this;
    }

    /**
     * @param string $templateEngine
     */
    public function setTemplateEngine(string $templateEngine): void
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @return string
     */
    public function getTemplateEngine(): string
    {
        if (!$this->templateEngine) {
            $this->templateEngine = TemplateEngine::FLUID_AND_MARKDOWN;
        }
        return $this->templateEngine;
    }
}
