<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@gebruederheitz.de>, Gebruederheitz
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
 * Test case for class Tx_Messenger_Domain_Model_SentMessage.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage Send a message to a group of people
 *
 * @author Fabien Udriot <fabien.udriot@gebruederheitz.de>
 */
class Tx_Messenger_Domain_Model_SentMessageTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Messenger_Domain_Model_SentMessage
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Messenger_Domain_Model_SentMessage();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getUserReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getUser()
		);
	}

	/**
	 * @test
	 */
	public function setUserForIntegerSetsUser() { 
		$this->fixture->setUser(12);

		$this->assertSame(
			12,
			$this->fixture->getUser()
		);
	}
	
	/**
	 * @test
	 */
	public function getMessageReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getMessage()
		);
	}

	/**
	 * @test
	 */
	public function setMessageForIntegerSetsMessage() { 
		$this->fixture->setMessage(12);

		$this->assertSame(
			12,
			$this->fixture->getMessage()
		);
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