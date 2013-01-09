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

require_once(t3lib_extMgm::extPath('messenger') . 'Tests/Unit/BaseTest.php');

/**
 * Test case for class Tx_Messenger_Utility_Message.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage Email templates
 *
 * @author Fabien Udriot <fudriot@cobweb.ch>
 */
class Tx_Messenger_Utility_MessageTest extends Tx_Messenger_BaseTest {

	/**
	 * @var Tx_Phpunit_Framework
	 */
	protected $testingFramework;

	/**
	 * @var Tx_Messenger_Utility_Message
	 */
	protected $fixture;

	/**
	 * @var array
	 */
	protected $recipients;

	/**
	 * @var string
	 */
	protected $attachment;

	/**
	 * @var array
	 */
	protected $markers;

	public function setUp() {
		parent::setUp();
		$name = uniqid('name');
		$this->recipients = array("$name@domain.ch" => $name);
		$this->markers = array(
			'first_name' => uniqid('first_name_'),
			'last_name' => uniqid('last_name_'),
		);
		$this->fixture = new Tx_Messenger_Utility_Message();
		$this->attachment = t3lib_extMgm::extPath('messenger') . 'Tests/Resources/Sample.pdf';
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function checkSettingsPropertyIsSet() {
		$this->assertAttributeNotEmpty(
			'settings',
			$this->fixture
		);
	}

	/**
	 * @test
	 */
	public function checkSenderPropertyIsSet() {
		$this->assertAttributeNotEmpty(
			'sender',
			$this->fixture
		);
	}

	/**
	 * @test
	 */
	public function setSenderReturnsCorrectValue() {
		$name = uniqid('name');
		$sender = array("$name@domain.ch" => $name);
		$this->fixture->setSender($sender);
		$this->assertAttributeEquals(
			$sender,
			'sender',
			$this->fixture
		);
	}

	/**
	 * @test
	 */
	public function getTemplateObjectReturnsCorrectValue() {
		$method = new ReflectionMethod(
			'Tx_Messenger_Utility_Message', 'getTemplateObject'
		);

		$method->setAccessible(TRUE);

		$this->assertInstanceOf(
			'Tx_Messenger_Domain_Model_MessageTemplate', $method->invokeArgs($this->fixture, array($this->identifier))
		);
	}

	/**
	 * @test
	 * @expectedException Tx_Messenger_Exception_RecordNotFoundException
	 */
	public function getTemplateObjectRaisesException() {
		$method = new ReflectionMethod(
			'Tx_Messenger_Utility_Message', 'getTemplateObject'
		);

		$method->setAccessible(TRUE);
		$method->invokeArgs($this->fixture, array(uniqid('identifier_')));
	}

	/**
	 * @test
	 */
	public function setDebugAffectRecipients() {
		$this->fixture->setRecipients($this->recipients);
		$this->assertAttributeEquals($this->recipients, 'recipients', $this->fixture);

		$templateObject = new Tx_Messenger_Domain_Model_MessageTemplate();

		$this->fixture->setDebug(TRUE, $templateObject);

		$this->assertTrue(is_int(strpos($templateObject->getBody(), 'DEBUG MODE')));
		$this->assertAttributeNotEquals($this->recipients, 'recipients', $this->fixture);
	}

	/**
	 * @test
	 */
	public function hasHtmlMethodReturnsTrueIfContentIsHtml() {
		$content = 'This is my message to the <b>PHPUnit</b>';
		$this->assertTrue($this->fixture->hasHtml($content));
	}

	/**
	 * @test
	 */
	public function hasHtmlMethodReturnsFalseIfContentIsText() {
		$content = 'This is my message to the PHPUnit';
		$this->assertFalse($this->fixture->hasHtml($content));
	}

	/**
	 * @test
	 */
	public function canSendMessageWithDebugFlag() {
		$mailSent = $this->fixture->setIdentifier($this->identifier)
			->setRecipients($this->recipients)
			->setMarkers($this->markers)
			->setDryRun(TRUE)
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function canSendMessageWithDebugFlagWithSysLanguageEqualsToOne() {
		$language = 1;
		$mailSent = $this->fixture->setIdentifier($this->identifier)
			->setRecipients($this->recipients)
			->setMarkers($this->markers)
			->setDryRun(TRUE)
			->setLanguage($language)
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function addAttachmentToMessage() {
		$this->assertAttributeEmpty('attachments', $this->fixture);
		$this->fixture->addAttachment($this->attachment);
		$this->assertAttributeNotEmpty('attachments', $this->fixture);
	}

	/**
	 * @test
	 * @expectedException Tx_Messenger_Exception_MissingFileException
	 */
	public function addAttachmentRaisesAnExceptionWhenFileDoesNotExistToMessage() {
		$attachment = '/unknown/file.pdf';
		$this->fixture->addAttachment($attachment);
	}

	/**
	 * @test
	 * @dataProvider propertyProvider
	 */
	public function settersReturnInstanceOfMessageObject($propertyName, $value, $setterName = 'set') {
		$method = $setterName . ucfirst($propertyName);
		$actual = call_user_func_array(array($this->fixture, $method), array($value));
		$this->assertTrue($actual instanceof Tx_Messenger_Utility_Message);
	}

	/**
	 * Provider
	 */
	public function propertyProvider() {
		return array(
			array('markers', NULL),
			array('language', NULL),
			array('layout', NULL),
			array('identifier', NULL),
			array('markers', NULL),
			array('attachment', t3lib_extMgm::extPath('messenger') . 'Tests/Resources/Sample.pdf', 'add'),
			array('dryRun', NULL),
		);
	}
}
?>