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
 * Test case for class \Vanilla\Messenger\Utility\Object.
 */
class ObjectTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	public function setUp() {
	}

	public function tearDown() {
	}

	/**
	 * @test
	 */
	public function stdClassObjectReturnsAnArrayHavingKeyFoo() {
		$mock = new stdClass();
		$mock->foo = uniqid();
		$actual = \Vanilla\Messenger\Utility\Object::toArray($mock);
		$this->assertArrayHasKey('foo', $actual);
	}

	/**
	 * @test
	 */
	public function feUserObjectReturnsAnArrayHavingKeyName() {
		$expected = uniqid();
		$fakeUser = new Tx_Extbase_Domain_Model_FrontendUser();
		$fakeUser->setName($expected);
		$actual = \Vanilla\Messenger\Utility\Object::toArray($fakeUser);
		$this->assertSame($expected, $actual['name']);
	}

}
?>