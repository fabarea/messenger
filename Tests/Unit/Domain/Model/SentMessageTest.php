<?php

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


/**
 * Test case for class \Fab\Messenger\Domain\Model\SentMessage.
 */
class SentMessageTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Fab\Messenger\Domain\Model\SentMessage
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new \Fab\Messenger\Domain\Model\SentMessage();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getContentReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setContentForStringSetsContent() {
		$this->fixture->setContent('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getSentTimeReturnsInitialValueForInt() { }

	/**
	 * @test
	 */
	public function setSentTimeForIntSetsSentTime() { }

}
?>