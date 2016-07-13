<?php
namespace Fab\Messenger\Domain\Model;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use Fab\Messenger\Exception\RecordNotFoundException;
use Fab\Messenger\Html2Text\TemplateEngine;

/**
 * Message Template representation
 */
class MessageTemplate extends AbstractEntity
{

    const TYPE_TEXT = 1;
    const TYPE_PAGE = 2;
    const TYPE_FILE = 3;

    /**
     * @var string
     */
    protected $qualifier;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var int
     */
    protected $sourcePage;

    /**
     * @var string
     */
    protected $sourceFile;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $subject;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $layoutBody;

    /**
     * @var \Fab\Messenger\Domain\Model\MessageLayout
     */
    protected $messageLayout;

    /**
     * @var string
     */
    protected $templateEngine;

    /**
     * @var \Fab\Messenger\Domain\Repository\MessageLayoutRepository
     * @inject
     */
    protected $messageLayoutRepository;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        $this->qualifier = empty($data['qualifier']) ? '' : $data['qualifier'];
        $this->type = empty($data['type']) ? 0 : (int)$data['type'];
        $this->sourcePage = empty($data['source_page']) ? 0 : $data['source_page'];
        $this->sourceFile = empty($data['source_file']) ? '' : $data['source_file'];
        $this->subject = empty($data['subject']) ? '' : $data['subject'];
        $this->body = empty($data['body']) ? '' : $data['body'];
        $this->messageLayout = empty($data['message_layout']) ? '' : $data['message_layout'];
    }

    /**
     * Returns the subject
     *
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the subject
     *
     * @param string $subject
     * @return void
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Returns the body according to the type of the message template.
     *
     * @throws \Exception
     * @return string $body
     */
    public function getBody()
    {

        if ($this->type === self::TYPE_PAGE) {
            // @todo use $crawler to fetch body content of page
            throw new \Exception('Messenger: not implemented', 1400517075);

        } elseif ($this->type === self::TYPE_FILE) {
            $file = GeneralUtility::getFileAbsFileName($this->sourceFile);
            if (!is_file($file)) {
                $message = sprintf('Messenger: I could not found file "%s"', $file);
                throw new \Exception($message, 1400517074);
            }
            $this->body = file_get_contents($file);
        }

        // Possible wrap body in Layout content.
        if ($this->messageLayout) {
            $this->body = str_replace('{BODY}', $this->body, $this->messageLayout->getContent());
        }
        return $this->body;
    }

    /**
     * Sets the body
     *
     * @param string $body
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string $qualifier
     */
    public function getQualifier()
    {
        return $this->qualifier;
    }

    /**
     * @param string $qualifier
     * @return void
     */
    public function setQualifier($qualifier)
    {
        $this->qualifier = $qualifier;
    }

    /**
     * @throws \Fab\Messenger\Exception\RecordNotFoundException
     * @return \Fab\Messenger\Domain\Model\MessageLayout
     */
    public function getMessageLayout()
    {
        if (!is_object($this->messageLayout)) {
            /** @var $layout \Fab\Messenger\Domain\Model\MessageLayout */
            $this->messageLayout = $this->messageLayoutRepository->findByUid($this->messageLayout);
            if (!$this->messageLayout) {
                $message = sprintf('No Email Layout record was found for identity "%s"', $this->messageLayout);
                throw new RecordNotFoundException($message, 1389779386);
            }
        }

        return $this->messageLayout;
    }

    /**
     * @param string $layout
     */
    public function setMessageLayout($layout)
    {
        $this->messageLayout = $layout;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getSourcePage()
    {
        return $this->sourcePage;
    }

    /**
     * @param int $sourcePage
     * @return $this
     */
    public function setSourcePage($sourcePage)
    {
        $this->sourcePage = $sourcePage;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }

    /**
     * @param string $sourceFile
     * @return $this
     */
    public function setSourceFile($sourceFile)
    {
        $this->sourceFile = $sourceFile;
        return $this;
    }

    /**
     * @param string $templateEngine
     */
    public function setTemplateEngine($templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @return string
     */
    public function getTemplateEngine()
    {
        if (empty($this->templateEngine)) {
            $this->templateEngine = TemplateEngine::FLUID_AND_MARKDOWN;
        }
        return $this->templateEngine;
    }
}
