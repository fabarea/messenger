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


require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger') . 'Tests/Unit/BaseTest.php');

/**
 * Test case for class \Fab\Messenger\Domain\Model\MessageTemplate.
 */
class MessageTemplateTest extends Tx_Messenger_BaseTest {

	/**
	 * @var \Fab\Messenger\Domain\Model\MessageTemplate
	 */
	protected $fixture;

	public function setUp(): void {
		parent::setUp();
		$this->fixture = new \Fab\Messenger\Domain\Model\MessageTemplate();
	}

	public function tearDown(): void {
		parent::tearDown();
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function setSubjectForStringSetsSubject(): void {
		$this->fixture->setSubject('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function getBodyReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setBodyForStringSetsBody(): void {
		$this->fixture->setBody('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getBody()
		);
	}

	/**
	 * @test
	 */
	public function getIdentifierReturnsInitialValueForString() {
	}

	/**
	 * @test
	 */
	public function setIdentifierForStringSetsIdentifier(): void {
		$this->fixture->setIdentifier('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getIdentifier()
		);
	}

	/**
	 * @test
	 */
	public function getMarkerTemplateReturnsDefaultMarker(): void {
		$method = new ReflectionMethod(
			'Fab\Messenger\Domain\Model\MessageTemplate', 'getMarkerTemplate'
		);

		$method->setAccessible(TRUE);
		$this->assertEquals('{template}', $method->invoke($this->fixture));
	}
}