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
 * Test case for class Tx_Messenger_ListManager_DemoListManager.
 *
 * @author Fabien Udriot <fabien.udriot@typo3.org>
 * @package TYPO3
 * @subpackage media
 */
class Tx_Messenger_ListManager_DemoListManagerTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	/**
	 * @var Tx_Messenger_ListManager_DemoListManager
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new Tx_Messenger_ListManager_DemoListManager();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getRecordsReturnANotEmptySetOfUsers() {
		$this->assertNotEmpty($this->fixture->findBy());
	}

	/**
	 * @test
	 */
	public function getTableHeaderReturnANotEmptySet() {
		$this->assertNotEmpty($this->fixture->getFields());
	}

	/**
	 * @test
	 */
	public function getRecordReturnsNotEmptyArrayForUidEqualsOne() {
		$this->assertNotEmpty($this->fixture->findByUid(1));
	}

	/**
	 * @test
	 */
	public function getRecipientInfoReturnsNotEmptyArrayForUidEqualsOne() {
		$this->assertNotEmpty($this->fixture->getRecipientInfo(1));
	}
}
?>