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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger') . 'Tests/Unit/BaseTest.php');

/**
 * Test case for class \Vanilla\Messenger\Service\Html2Text.
 */
class Html2TextTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \Vanilla\Messenger\Service\Html2Text
	 */
	protected $fixture;

	/**
	 * @var array
	 */
	protected $recipients;

	/**
	 * @var array
	 */
	protected $markers;

	public function setUp() {
		$this->fixture = new \Vanilla\Messenger\Service\Html2Text();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function convertReturnsTextWithRegexpConverter() {

		$input = 'End of the <span>comprehensible</span> World';
		$expected = 'End of the comprehensible World';
		$converter = new \Vanilla\Messenger\Html2Text\RegexpStrategy();
		$this->fixture->setConverter($converter);

		$this->assertEquals($expected, $this->fixture->convert($input));
	}

	/**
	 * @test
	 */
	public function convertReturnsTextWithLynxConverter() {
		$input = 'End of the <span>comprehensible</span> World';
		$expected = 'End of the comprehensible World';
		$converter = new \Vanilla\Messenger\Html2Text\LynxStrategy();
		$lynxPath = '/opt/local/bin/lynx'; // @to-improve corresponds to Fabien's environment
		$converter->setLynx($lynxPath);
		$this->fixture->setConverter($converter);

		$this->assertEquals($expected, $this->fixture->convert($input));
	}

	/**
	 * @test
	 */
	public function findBestConverterReturnsRegexpConverter() {
		$converter = $this->fixture->findBestConverter();
		$this->assertTrue($converter instanceof \Vanilla\Messenger\Html2Text\RegexpStrategy);
	}
}
?>