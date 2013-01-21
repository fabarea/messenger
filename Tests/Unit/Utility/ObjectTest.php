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
 * Test case for class Tx_Messenger_Utility_Object.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage messenger
 *
 * @author Fabien Udriot <fudriot@cobweb.ch>
 */
class Tx_Messenger_Utility_ObjectTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

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
		$actual = Tx_Messenger_Utility_Object::toArray($mock);
		$this->assertArrayHasKey('foo', $actual);
	}

	/**
	 * @test
	 */
	public function feUserObjectReturnsAnArrayHavingKeyName() {
		$expected = uniqid();
		$fakeUser = new Tx_Extbase_Domain_Model_FrontendUser();
		$fakeUser->setName($expected);
		$actual = Tx_Messenger_Utility_Object::toArray($fakeUser);
		$this->assertSame($expected, $actual['name']);
	}

}
?>