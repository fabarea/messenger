<?php
namespace Vanilla\Messenger\Domain\Model;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use Vanilla\Messenger\Exception\RecordNotFoundException;

/**
 * Message Template representation
 */
class MessageTemplate extends AbstractEntity {

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
	 * @var \Vanilla\Messenger\Domain\Model\MessageLayout
	 */
	protected $messageLayout;

	/**
	 * @var \Vanilla\Messenger\Domain\Repository\MessageLayoutRepository
	 * @inject
	 */
	protected $messageLayoutRepository;

	/**
	 * Constructor
	 */
	public function __construct(array $data = array()) {
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
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets the subject
	 *
	 * @param string $subject
	 * @return void
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * Returns the body according to the type of the message template.
	 *
	 * @throws \Exception
	 * @return string $body
	 */
	public function getBody() {

		if ($this->type === self::TYPE_PAGE) {
			// @todo use $crawler to fetch body content of page
			throw new \Exception('Messenger: not implemented', 1400517075);

		} elseif ($this->type === self::TYPE_FILE) {
			$file = GeneralUtility::getFileAbsFileName($this->sourceFile);
			if (!is_file($file)) {
				throw new \Exception('Messenger: I could not found file ' . $file, 1400517074);
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
	public function setBody($body) {
		$this->body = $body;
	}

	/**
	 * @return string $qualifier
	 */
	public function getQualifier() {
		return $this->qualifier;
	}

	/**
	 * @param string $qualifier
	 * @return void
	 */
	public function setQualifier($qualifier) {
		$this->qualifier = $qualifier;
	}

	/**
	 * @throws \Vanilla\Messenger\Exception\RecordNotFoundException
	 * @return \Vanilla\Messenger\Domain\Model\MessageLayout
	 */
	public function getMessageLayout() {
		if (!is_object($this->messageLayout)) {
			/** @var $layout \Vanilla\Messenger\Domain\Model\MessageLayout */
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
	public function setMessageLayout($layout) {
		$this->messageLayout = $layout;
	}

	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param int $type
	 * @return $this
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSourcePage() {
		return $this->sourcePage;
	}

	/**
	 * @param int $sourcePage
	 * @return $this
	 */
	public function setSourcePage($sourcePage) {
		$this->sourcePage = $sourcePage;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSourceFile() {
		return $this->sourceFile;
	}

	/**
	 * @param int $sourceFile
	 * @return $this
	 */
	public function setSourceFile($sourceFile) {
		$this->sourceFile = $sourceFile;
		return $this;
	}
}
