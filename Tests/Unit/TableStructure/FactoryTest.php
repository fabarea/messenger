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
 * Test case for class Tx_Messenger_Utility_ConfigurationTest.
 *
 * @author Fabien Udriot <fabien.udriot@typo3.org>
 * @package TYPO3
 * @subpackage media
 */
class Tx_Messenger_TableStructure_FactoryTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	public function setUp() {
	}

	public function tearDown() {
	}

	/**
	 * @test
	 */
	public function getInstanceMethodOfTableStructureReturnsAnInstanceOfATableStructureInterface() {
		$instance = Tx_Messenger_TableStructure_Factory::getInstance('Tx_Messenger_TableStructure_DemoTableStructure');
		$this->assertTrue($instance instanceof Tx_Messenger_Interface_TableStructureInterface);
	}
}
?>