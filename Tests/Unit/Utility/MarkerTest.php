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
 * Test case for class Tx_Messenger_Utility_Marker.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage messenger
 *
 * @author Fabien Udriot <fudriot@cobweb.ch>
 */
class Tx_Messenger_Utility_MarkerTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	/**
	 * @var string
	 */
	private $input;

	/**
	 * @var string
	 */
	private $inputWithoutMarkers;

	/**
	 * @var string
	 */
	private $marker1;

	/**
	 * @var string
	 */
	private $marker2;

	/**
	 * @var array
	 */
	private $markers;

	/**
	 * @var Tx_Messenger_Utility_Marker
	 */
	private $fixture;

	public function setUp() {
		$this->input = 'Lorem ipsum dolor sit amet, {marker_1}, ante vel tempus {marker_2}';
		$this->inputWithoutMarkers = 'Lorem ipsum dolor sit amet, , ante vel tempus ';

		$this->marker1 = uniqid('marker1_');
		$this->marker2 = uniqid('marker2_');
		$this->markers = array(
			'marker_1' => $this->marker1,
			'marker_2' => $this->marker2,
		);
		$this->fixture = new Tx_Messenger_Utility_Marker();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function propertyViewIsCorrectlySet() {
		$this->assertAttributeInstanceOf(
			'Tx_Fluid_View_StandaloneView',
			'view',
			$this->fixture
		);
	}

	/**
	 * @test
	 */
	public function substituteStringWithEmptyMarker() {
		$markers = array();
		$output = $this->fixture->substitute($this->input, $markers);
		$this->assertEquals($this->inputWithoutMarkers, $output);
	}

	/**
	 * @test
	 */
	public function substituteStringWithMarkers() {
		$output = $this->fixture->substitute($this->input, $this->markers);
		$this->assertGreaterThan(0, strpos($output, $this->marker1));
		$this->assertGreaterThan(0, strpos($output, $this->marker2));
	}

	/**
	 * @test
	 */
	public function substituteStringWithFormatEqualsToHtml() {
		$output = $this->fixture->substitute($this->input, $this->markers, 'html');
		$this->assertGreaterThan(0, strpos($output, $this->marker1));
		$this->assertGreaterThan(0, strpos($output, $this->marker2));
	}

	/**
	 * @test
	 */
	public function getRteConfigurationReturnsANotEmptyArray() {

		$method = new ReflectionMethod(
			'Tx_Messenger_Utility_Marker', 'getRteConfiguration'
		);

		$method->setAccessible(TRUE);
		$actual = $method->invoke($this->fixture);
		$this->assertNotEmpty($actual);
	}

}
?>