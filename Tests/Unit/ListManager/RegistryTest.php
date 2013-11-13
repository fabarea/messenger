<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Media development team <typo3-project-media@lists.typo3.org>
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
 * Test case for class \TYPO3\CMS\Messenger\ListManager\Registry.
 *
 * @author Fabien Udriot <fabien.udriot@typo3.org>
 * @package TYPO3
 * @subpackage media
 */
class RegistryTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \TYPO3\CMS\Messenger\ListManager\Registry
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new \TYPO3\CMS\Messenger\ListManager\Registry();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function theRegistryIsEmptyByDefault() {
		$this->assertEmpty($this->fixture->get());
	}

	/**
	 * @test
	 */
	public function isRegisterReturnsFalseForANotRegisteredRandomValue() {
		$this->assertFalse($this->fixture->isRegistered(uniqid()));
	}

	/**
	 * @test
	 */
	public function isRegisterReturnsTrueForARegisteredRandomValue() {
		$listManager = uniqid();
		$this->fixture->add($listManager, uniqid());
		$this->assertArrayHasKey($listManager, $this->fixture->get());
	}

	/**
	 * @test
	 */
	public function addANewValueIntoTheRegistryAndCheckTheRegistryContainsIt() {
		$expected = uniqid();
		$this->fixture->add($expected, uniqid());
		$this->assertEquals(1, count($this->fixture->get()));
	}

	/**
	 * @test
	 */
	public function addANewValueIntoTheRegistryAndRemoveItFromTheRegistry() {
		$expected = uniqid();
		$this->fixture->add($expected, uniqid());
		$this->fixture->remove($expected);
		$this->assertEmpty($this->fixture->get());
	}

	/**
	 * @test
	 */
	public function countMethodReturnsTwoWhenOneElementIsAdded() {
		$this->fixture->add(uniqid(), uniqid());
		$this->fixture->add(uniqid(), uniqid());
		$this->assertEquals(2, $this->fixture->count());
	}
	}
?>