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
 * Test case for class Tx_Messenger_Domain_Model_Message.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage messenger
 *
 * @author Fabien Udriot <fudriot@cobweb.ch>
 */
class Tx_Messenger_Domain_Model_MessageTest extends Tx_Messenger_BaseTest {

	/**
	 * @var Tx_Phpunit_Framework
	 */
	protected $testingFramework;

	/**
	 * @var Tx_Messenger_Domain_Model_Message
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

	/**
	 * @var string
	 */
	protected $mboxFile;

	public function setUp() {
		parent::setUp();
		$name = uniqid('name');
		$this->recipients = array("$name@domain.ch" => $name);
		$this->markers = array(
			'first_name' => uniqid('first_name_'),
			'last_name' => uniqid('last_name_'),
		);
		$this->fixture = new Tx_Messenger_Domain_Model_Message();
		$this->attachment = t3lib_extMgm::extPath('messenger') . 'Tests/Resources/Sample.pdf';

		// Compute temporary directory.
		$temporaryDirectory = PATH_site . 'typo3temp'; // ini_get('upload_tmp_dir');
		if (!is_dir($temporaryDirectory)) {
			$temporaryDirectory = sys_get_temp_dir();
		}
		$this->mboxFile = $temporaryDirectory . '/mbox';

		// configuration for SwiftMail
		$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'mbox';
		$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_mbox_file'] = $this->mboxFile;
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->fixture);
		if (file_exists($this->mboxFile)) {
			unlink($this->mboxFile);
		}
	}

	/**
	 * @test
	 */
	public function checkSenderPropertyIsNotEmptyByDefault() {
		$this->assertAttributeNotEmpty(
			'sender',
			$this->fixture
		);
	}

	/**
	 * @test
	 */
	public function setSenderCanBeSetWithRandomValue() {
		$name = uniqid('name');
		$sender = array($name . "@domain.ch" => $name);
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
	public function getMessageTemplateReturnsMessageTemplateForUidOfTypeInt() {
		$method = new ReflectionMethod(
			'Tx_Messenger_Domain_Model_Message', 'getMessageTemplate'
		);

		$this->fixture->setMessageTemplate($this->uidTemplate);
		$method->setAccessible(TRUE);
		$this->assertInstanceOf(
			'Tx_Messenger_Domain_Model_MessageTemplate', $method->invoke($this->fixture)
		);
	}

	/**
	 * @test
	 */
	public function getMessageTemplateReturnsMessageTemplateForUidOfTypeString() {
		$method = new ReflectionMethod(
			'Tx_Messenger_Domain_Model_Message', 'getMessageTemplate'
		);

		$this->fixture->setMessageTemplate((string) $this->uidTemplate);
		$method->setAccessible(TRUE);
		$this->assertInstanceOf(
			'Tx_Messenger_Domain_Model_MessageTemplate', $method->invoke($this->fixture)
		);
	}

	/**
	 * @test
	 */
	public function getMessageTemplateReturnsMessageTemplateForIdentifier() {
		$method = new ReflectionMethod(
			'Tx_Messenger_Domain_Model_Message', 'getMessageTemplate'
		);

		$this->fixture->setMessageTemplate($this->identifier);
		$method->setAccessible(TRUE);
		$this->assertInstanceOf(
			'Tx_Messenger_Domain_Model_MessageTemplate', $method->invoke($this->fixture)
		);
	}

	/**
	 * @test
	 * @expectedException Tx_Messenger_Exception_RecordNotFoundException
	 */
	public function setMessageTemplateRaisesException() {
		$method = new ReflectionMethod(
			'Tx_Messenger_Domain_Model_Message', 'setMessageTemplate'
		);

		$method->setAccessible(TRUE);
		$method->invokeArgs($this->fixture, array(uniqid()));
	}

	/**
	 * @test
	 */
	public function setRecipientPropertyIsSetBySetRecipientMethod() {
		$this->fixture->setRecipients($this->recipients);
		$this->assertAttributeEquals($this->recipients, 'recipients', $this->fixture);
	}

	/**
	 * @test
	 */
	public function setRecipientPropertyCanBeSetWithEmail() {
		$email = uniqid() . '@test.com';
		$this->fixture->setRecipients($email);
	}

	/**
	 * @test
	 */
	public function getRecipientsForSimulationIsNotEmptyByDefault() {
		$this->fixture->setRecipients($this->recipients);

		$method = new ReflectionMethod(
			'Tx_Messenger_Domain_Model_Message', 'getRecipientsForSimulation'
		);

		$method->setAccessible(TRUE);
		$this->assertNotEmpty($method->invoke($this->fixture));
	}

	/**
	 * @test
	 */
	public function getMessageBodyForSimulationPrependsBodyMessage() {
		$method = new ReflectionMethod(
			'Tx_Messenger_Domain_Model_Message', 'getMessageBodyForSimulation'
		);

		$method->setAccessible(TRUE);
		$actual = $method->invokeArgs($this->fixture, array(uniqid()));
		$this->assertContains('this message is for testing purposes.', $actual);
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
	public function canSendMessageWithSimulateFlagUsingMboxTransport() {
		$mailSent = $this->fixture->setMessageTemplate($this->identifier)
			->setRecipients($this->recipients)
			->setMarkers($this->markers)
			->simulate()
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function canSendMessageWithSetMarkers() {
		$mailSent = $this->fixture->setMessageTemplate($this->identifier)
			->setRecipients($this->recipients)
			->setMarkers($this->markers)
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function canSendMessageWithNoSetMarkers() {
		$mailSent = $this->fixture->setMessageTemplate($this->identifier)
			->setRecipients($this->recipients)
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function canSendMessageWithDebugFlagWithSysLanguageEqualsToOne() {
		$language = 1;
		$mailSent = $this->fixture->setMessageTemplate($this->identifier)
			->setRecipients($this->recipients)
			->setMarkers($this->markers)
			->simulate()
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
		$this->assertTrue($actual instanceof Tx_Messenger_Domain_Model_Message);
	}

	/**
	 * Provider
	 */
	public function propertyProvider() {
		return array(
			array('markers', NULL),
			array('language', NULL),
			array('layout', NULL),
			array('markers', NULL),
			array('attachment', t3lib_extMgm::extPath('messenger') . 'Tests/Resources/Sample.pdf', 'add'),
		);
	}
}
?>