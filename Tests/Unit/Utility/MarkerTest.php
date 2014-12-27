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


/**
 * Test case for class \Vanilla\Messenger\Utility\Marker.
 */
class MarkerTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

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
	 * @var \Vanilla\Messenger\Utility\Marker
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
		$this->fixture = new \Vanilla\Messenger\Utility\Marker();
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
		// Make sure to have the RTE loaded. Can be done in the settings with Extension Manager.
		$this->assertEquals('<p>' . $this->inputWithoutMarkers . '</p>', $output);
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
			'Vanilla\Messenger\Utility\Marker', 'getRteConfiguration'
		);

		$method->setAccessible(TRUE);
		$actual = $method->invoke($this->fixture);
		$this->assertNotEmpty($actual);
	}

}
?>