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
 * Test case for class Tx_Messenger_ViewHelpers_Table_RowViewHelper.
 *
 * @author Fabien Udriot <fabien.udriot@typo3.org>
 * @package TYPO3
 * @subpackage media
 */
class Tx_Messenger_ViewHelpers_Table_RowViewHelperTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	/**
	 * @var Tx_Messenger_ViewHelpers_Table_RowViewHelper
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new Tx_Messenger_ViewHelpers_Table_RowViewHelper();

	}

	public function tearDown() {
	}

	/**
	 * @test
	 */
	public function renderReturnsStringThatContainsThTags() {
		$recipients = Tx_Messenger_ListManager_Factory::getInstance()->findBy();
		$actual = $this->fixture->render($recipients[0]);

		$expected = count(Tx_Messenger_ListManager_Factory::getInstance()->getFields());
		$this->assertEquals($expected, preg_match_all('/<td/isU', $actual, $matches));
	}

	/**
	 * @test
	 */
	public function getStyleAttributesReturnsValidAttribute() {
		$tableHeader = array(
			'width' => '60px',
			'style' => 'background-color: red',
		);

		$method = new ReflectionMethod(
			'Tx_Messenger_ViewHelpers_Table_RowViewHelper', 'getStyleAttribute'
		);
		$method->setAccessible(TRUE);
		$actual = $method->invokeArgs($this->fixture, array($tableHeader));

		$expected = 'style="background-color: red;width: 60px"';
		$this->assertEquals($expected, $actual);
	}

}
?>