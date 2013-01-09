<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Fabien Udriot <fudriot@cobweb.ch>, Cobweb
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
 * Test case for class Tx_Messenger_Strategy_Html2Text_LynxStrategy.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage messenger
 *
 * @author Fabien Udriot <fudriot@cobweb.ch>
 */
class Tx_Messenger_Strategy_Html2Text_LynxStrategyTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	/**
	 * @var Tx_Messenger_Strategy_Html2Text_LynxStrategy
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new Tx_Messenger_Strategy_Html2Text_LynxStrategy();
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
?>