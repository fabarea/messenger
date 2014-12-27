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
 * Test case for class \Fab\Messenger\Html2Text\RegexpStrategy.
 */
class RegexpStrategyTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \Fab\Messenger\Html2Text\RegexpStrategy
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new \Fab\Messenger\Html2Text\RegexpStrategy();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function convertMethodReturnsTextIfLynxPathIsDefined() {
		$input = 'hello my dear <b>friend</b>';
		$expected = 'hello my dear FRIEND';
		$actual = $this->fixture->convert($input);
		$this->assertEquals($expected, $actual);
	}
}
?>