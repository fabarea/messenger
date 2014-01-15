<?php
namespace TYPO3\CMS\Messenger\Domain\Model;
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
use TYPO3\CMS\Messenger\Exception\RecordNotFoundException;

/**
 * Message Template representation
 */
class MessageTemplate extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var string
	 */
	protected $identifier;

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
	 * @var \TYPO3\CMS\Messenger\Domain\Model\MessageLayout
	 */
	protected $messageLayout;

	/**
	 * @var \TYPO3\CMS\Messenger\Domain\Repository\MessageLayoutRepository
	 * @inject
	 */
	protected $messageLayoutRepository;

	/**
	 * Constructor
	 */
	public function __construct(array $data = array()) {
		$this->identifier = !empty($data['identifier']) ? $data['identifier'] : '';
		$this->subject = !empty($data['subject']) ? $data['subject'] : '';
		$this->body = !empty($data['body']) ? $data['body'] : '';
		$this->messageLayout = !empty($data['layout_template']) ? $data['layout_template'] : '';
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
	 * Returns the body
	 *
	 * @return string $body
	 */
	public function getBody() {
		if ($this->messageLayout) {
			$this->body = str_replace($this->getMarkerTemplate(), $this->body, $this->getLayoutContent());
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
	 * Returns the identifier
	 *
	 * @return string $identifier
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Sets the identifier
	 *
	 * @param string $identifier
	 * @return void
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * @throws \TYPO3\CMS\Messenger\Exception\RecordNotFoundException
	 * @return \TYPO3\CMS\Messenger\Domain\Model\MessageLayout
	 */
	public function getMessageLayout() {
		if (!is_object($this->messageLayout)) {
			/** @var $layout \TYPO3\CMS\Messenger\Domain\Model\MessageLayout */
			$this->messageLayout = $this->messageLayoutRepository->findByUid($this->messageLayout);
			if (!$this->messageLayout) {
				$message = sprintf('No Email Layout record was found for identity "%s"', $this->messageLayout);
				throw new RecordNotFoundException($message, 1350124207);
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
	 * Get the marker that will be replaced by the body in a Layout.
	 * @return string
	 */
	protected function getMarkerTemplate() {
		$marker = \TYPO3\CMS\Messenger\Utility\Configuration::getInstance()->get('markerReplacedInLayout');
		return sprintf('{%s}', $marker);
	}

}
?>