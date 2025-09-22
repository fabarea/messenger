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


use TYPO3\CMS\Core\Core\Environment;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger') . 'Tests/Unit/BaseTest.php');

/**
 * Test case for class \Fab\Messenger\Domain\Model\Message.
 */
class MessageTest extends Tx_Messenger_BaseTest {

	/**
	 * @var Tx_Phpunit_Framework
	 */
	protected $testingFramework;

	/**
	 * @var \Fab\Messenger\Domain\Model\Message
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

	public function setUp(): void {
		parent::setUp();
		$name = uniqid('name');
		$this->recipients = array("$name@domain.ch" => $name);
		$this->markers = array(
			'first_name' => uniqid('first_name_'),
			'last_name' => uniqid('last_name_'),
		);
		$this->fixture = new \Fab\Messenger\Domain\Model\Message();
		$this->attachment = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger') . 'Tests/Resources/Sample.pdf';

		// Compute temporary directory.
		$temporaryDirectory = Environment::getPublicPath() . '/typo3temp'; // ini_get('upload_tmp_dir');
		if (!is_dir($temporaryDirectory)) {
			$temporaryDirectory = sys_get_temp_dir();
		}
		$this->mboxFile = $temporaryDirectory . '/mbox';

		// configuration for SwiftMail
		$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'mbox';
		$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_mbox_file'] = $this->mboxFile;
	}

	public function tearDown(): void {
		parent::tearDown();
		unset($this->fixture);
		if (file_exists($this->mboxFile)) {
			unlink($this->mboxFile);
		}
	}

	/**
	 * @test
	 */
	public function checkSenderPropertyIsNotEmptyByDefault(): void {
		$this->assertAttributeNotEmpty(
			'sender',
			$this->fixture
		);
	}

	/**
	 * @test
	 */
	public function setSenderCanBeSetWithRandomValue(): void {
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
	public function getTemplateReturnsMessageTemplateForUidOfTypeInt(): void {
		$method = new ReflectionMethod(
			'Fab\Messenger\Domain\Model\Message', 'getTemplate'
		);

		$this->fixture->setMessageTemplate($this->uidTemplate);
		$method->setAccessible(TRUE);
		$this->assertInstanceOf(
			'Fab\Messenger\Domain\Model\MessageTemplate', $method->invoke($this->fixture)
		);
	}

	/**
	 * @test
	 */
	public function getTemplateReturnsMessageTemplateForUidOfTypeString(): void {
		$method = new ReflectionMethod(
			'Fab\Messenger\Domain\Model\Message', 'getTemplate'
		);

		$this->fixture->setMessageTemplate((string) $this->uidTemplate);
		$method->setAccessible(TRUE);
		$this->assertInstanceOf(
			'Fab\Messenger\Domain\Model\MessageTemplate', $method->invoke($this->fixture)
		);
	}

	/**
	 * @test
	 */
	public function getTemplateReturnsMessageTemplateForIdentifier(): void {
		$method = new ReflectionMethod(
			'Fab\Messenger\Domain\Model\Message', 'getTemplate'
		);

		$this->fixture->setMessageTemplate($this->identifier);
		$method->setAccessible(TRUE);
		$this->assertInstanceOf(
			'Fab\Messenger\Domain\Model\MessageTemplate', $method->invoke($this->fixture)
		);
	}

	/**
	 * @test
	 * @expectedException \Fab\Messenger\Exception\RecordNotFoundException
	 */
	public function setTemplateRaisesException(): void {
		$method = new ReflectionMethod(
			'Fab\Messenger\Domain\Model\Message', 'setTemplate'
		);

		$method->setAccessible(TRUE);
		$method->invokeArgs($this->fixture, array(uniqid()));
	}

	/**
	 * @test
	 */
	public function setRecipientPropertyIsSetBySetRecipientMethod(): void {
		$this->fixture->setTo($this->recipients);
		$this->assertAttributeEquals($this->recipients, 'recipients', $this->fixture);
	}

	/**
	 * @test
	 */
	public function setRecipientPropertyCanBeSetWithEmail(): void {
		$email = uniqid() . '@test.com';
		$this->fixture->setTo($email);
	}

	/**
	 * @test
	 */
	public function getRecipientsForSimulationIsNotEmptyByDefault(): void {
		$this->fixture->setTo($this->recipients);

		$method = new ReflectionMethod(
			'Fab\Messenger\Domain\Model\Message', 'getRecipientsForSimulation'
		);

		$method->setAccessible(TRUE);
		$this->assertNotEmpty($method->invoke($this->fixture));
	}

	/**
	 * @test
	 */
	public function getMessageBodyForSimulationPrependsBodyMessage(): void {
		$method = new ReflectionMethod(
			'Fab\Messenger\Domain\Model\Message', 'getMessageBodyForSimulation'
		);

		$method->setAccessible(TRUE);
		$actual = $method->invokeArgs($this->fixture, array(uniqid()));
		$this->assertContains('this message is for testing purposes.', $actual);
	}

	/**
	 * @test
	 */
	public function hasHtmlMethodReturnsTrueIfContentIsHtml(): void {
		$content = 'This is my message to the <b>PHPUnit</b>';
		$this->assertTrue($this->fixture->hasHtml($content));
	}

	/**
	 * @test
	 */
	public function hasHtmlMethodReturnsFalseIfContentIsText(): void {
		$content = 'This is my message to the PHPUnit';
		$this->assertFalse($this->fixture->hasHtml($content));
	}

	/**
	 * @test
	 */
	public function canSendMessageWithSimulateFlagUsingMboxTransport(): void {
		$mailSent = $this->fixture->setMessageTemplate($this->identifier)
			->setTo($this->recipients)
			->setMarkers($this->markers)
			->simulate()
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function canSendMessageWithSetMarkers(): void {
		$mailSent = $this->fixture->setMessageTemplate($this->identifier)
			->setTo($this->recipients)
			->setMarkers($this->markers)
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function canSendMessageWithNoSetMarkers(): void {
		$mailSent = $this->fixture->setMessageTemplate($this->identifier)
			->setTo($this->recipients)
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function canSendMessageWithDebugFlagWithSysLanguageEqualsToOne(): void {
		$language = 1;
		$mailSent = $this->fixture->setMessageTemplate($this->identifier)
			->setTo($this->recipients)
			->setMarkers($this->markers)
			->simulate()
			->setLanguage($language)
			->send();
		$this->assertTrue($mailSent);
	}

	/**
	 * @test
	 */
	public function addAttachmentToMessage(): void {
		$this->assertAttributeEmpty('attachments', $this->fixture);
		$this->fixture->addAttachment($this->attachment);
		$this->assertAttributeNotEmpty('attachments', $this->fixture);
	}

	/**
	 * @test
	 * @expectedException \Fab\Messenger\Exception\MissingFileException
	 */
	public function addAttachmentRaisesAnExceptionWhenFileDoesNotExistToMessage(): void {
		$attachment = '/unknown/file.pdf';
		$this->fixture->addAttachment($attachment);
	}

	/**
	 * @test
	 */
	public function setMarkerWithStdClassObjectGetConvertedToArray(): void {
		$fakeObject = new stdClass();
		$fakeObject->foo = uniqid();
		$this->fixture->setMarkers($fakeObject);
		$this->assertArrayHasKey('foo', $this->fixture->getMarkers());
	}

	/**
	 * @test
	 */
	public function setMarkerWithFeUserObjectGetConvertedToArray(): void {
		$expected = uniqid();
		$fakeUser = new Tx_Extbase_Domain_Model_FrontendUser();
		$fakeUser->setName($expected);
		$this->fixture->setMarkers($fakeUser);
		$actual = $this->fixture->getMarkers();
		$this->assertSame($expected, $actual['name']);
	}


	/**
	 * @test
	 * @dataProvider propertyProvider
	 */
	public function settersReturnInstanceOfMessageObject($propertyName, $value, $setterName = 'set'): void {
		$method = $setterName . ucfirst($propertyName);
		$actual = call_user_func_array(array($this->fixture, $method), array($value));
		$this->assertTrue($actual instanceof \Fab\Messenger\Domain\Model\Message);
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
			array('attachment', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('messenger') . 'Tests/Resources/Sample.pdf', 'add'),
		);
	}
}