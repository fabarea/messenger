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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger') . 'Tests/Unit/BaseTest.php');

/**
 * Test case for class \TYPO3\CMS\Messenger\Utility\Html2Text.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage messenger
 *
 * @author Fabien Udriot <fudriot@cobweb.ch>
 */
class Html2TextTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \TYPO3\CMS\Messenger\Utility\Html2Text
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
		$this->fixture = new \TYPO3\CMS\Messenger\Utility\Html2Text();
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
		$converter = new \TYPO3\CMS\Messenger\Strategy\Html2Text\RegexpStrategy();
		$this->fixture->setConverter($converter);

		$this->assertEquals($expected, $this->fixture->convert($input));
	}

	/**
	 * @test
	 */
	public function convertReturnsTextWithLynxConverter() {
		$input = 'End of the <span>comprehensible</span> World';
		$expected = 'End of the comprehensible World';
		$converter = new \TYPO3\CMS\Messenger\Strategy\Html2Text\LynxStrategy();
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
		$this->assertTrue($converter instanceof \TYPO3\CMS\Messenger\Strategy\Html2Text\RegexpStrategy);
	}
}
?>