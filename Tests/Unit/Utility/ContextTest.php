<?php

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
 *  the Free Software Foundation; either version 2 of the License, or
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

/**
 * Test case for class \Vanilla\Messenger\Utility\Context.
 */
class ContextTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var Tx_Phpunit_Framework
	 */
	protected $testingFramework;

	/**
	 * @var \Vanilla\Messenger\Utility\Context
	 */
	private $fixture;

	public function setUp() {

		$this->testingFramework = new Tx_Phpunit_Framework('tx_messenger_domain_model_messagetemplate');

		$pid = $this->testingFramework->createFrontEndPage(0, array('title' => 'foo'));
		$this->testingFramework->createTemplate($pid, array('root' => 1));
		$this->testingFramework->createFakeFrontEnd($pid);

		$this->fixture = new \Vanilla\Messenger\Utility\Context();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture, $this->testingFramework);
	}

	/**
	 * @test
	 */
	public function propertyLanguageReturnsDefaultSysLanguage() {
		$actual = $this->fixture->getLanguage();
		$this->assertEquals(0, $actual);
	}

	/**
	 * @test
	 */
	public function propertyLanguageCanBeSetAndGet() {
		$random = rand();
		$this->fixture->setLanguage($random);
		$this->assertEquals($random, $this->fixture->getLanguage());
	}

	/**
	 * @test
	 */
	public function propertyNameReturnsDevelopmentAsDefault() {
		$this->assertEquals('Development', $this->fixture->getName());
	}

	/**
	 * @test
	 */
	public function propertyNameCanBeSetAndGet() {
		$random = uniqid('Context_');
		$this->fixture->setName($random);
		$this->assertEquals($random, $this->fixture->getName());
	}

	/**
	 * @test
	 */
	public function isProductionContextReturnsTrue() {
		$this->fixture->setName('Production');
		$this->assertTrue($this->fixture->isProduction());
	}

	/**
	 * @test
	 */
	public function isProductionContextReturnsFalse() {
		$this->fixture->setName(uniqid('Context_'));
		$this->assertFalse($this->fixture->isProduction());
	}

	/**
	 * @test
	 */
	public function isContextSendingEmailsReturnsFalseInDevelopmentContext() {
		$this->assertFalse($this->fixture->isContextSendingEmails());
	}

	/**
	 * @test
	 */
	public function isContextSendingEmailsReturnsTrueInProductionContext() {
		$this->fixture->setName('Production');
		$this->assertTrue($this->fixture->isContextSendingEmails());
	}

}
?>