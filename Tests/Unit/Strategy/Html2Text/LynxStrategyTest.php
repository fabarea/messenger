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

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Test case for class \Fab\Messenger\Html2Text\LynxStrategy.
 */
class LynxStrategyTest extends UnitTestCase {

	/**
	 * @var \Fab\Messenger\Html2Text\LynxStrategy
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new \Fab\Messenger\Html2Text\LynxStrategy();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function lynxPropertyCanBeSetAndGet() {
		$lynxPath = uniqid('_path_');
		$this->fixture->setLynx($lynxPath);
		$this->assertEquals($lynxPath, $this->fixture->getLynx());
	}

	/**
	 * @test
	 */
	public function convertMethodReturnsTextIfLynxPathIsDefined() {
		$input = 'hello my dear <b>friend</b>';
		$lynxPath = '/opt/local/bin/lynx'; // @to-improve corresponds to Fabien's environment
		$this->fixture->setLynx($lynxPath);
		$actual = $this->fixture->convert($input);

		$expected = 'hello my dear friend';
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function convertMethodReturnsEmptyStringIfLynxPathIsNotDefined() {
		$input = 'hello my dear <b>friend</b>';
		$this->fixture->setLynx('');
		$actual = $this->fixture->convert($input);

		$expected = '';
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function detectIfLynxIsInstalled() {
		$this->assertFalse($this->fixture->available());
		$this->fixture->setLynx(uniqid('lynx'));
		$this->assertTrue($this->fixture->available());
	}
}