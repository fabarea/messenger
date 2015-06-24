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

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Group together some utility for testing purposes
 */
class Tx_Messenger_BaseTest extends UnitTestCase {

	/**
	 * @var Tx_Phpunit_Framework
	 */
	protected $testingFramework;

	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var string
	 */
	protected $subjectTranslated;

	/**
	 * @var string
	 */
	protected $body;

	/**
	 * @var string
	 */
	protected $bodyTranslated;

	/**
	 * @var string
	 */
	protected $layoutIdentifier;

	/**
	 * @var string
	 */
	protected $layoutContent;

	/**
	 * @var string
	 */
	protected $layoutContentTranslated;

	/**
	 * @var int
	 */
	protected $uidTemplate;

	/**
	 * @var int
	 */
	protected $uidLayout;


	public function setUp() {
		$this->testingFramework = new Tx_Phpunit_Framework('tx_messenger');

		$pid = $this->testingFramework->createFrontEndPage(0, array('title' => 'foo'));
		$this->testingFramework->createTemplate($pid, array('root' => 1));
		$this->testingFramework->createFakeFrontEnd($pid);

		$this->identifier = uniqid('identifier_');
		$this->subject = uniqid() . ' Unit Test email to {first_name} {last_name}';
		$this->subjectTranslated = 'Translated Content Element: ' . $this->subject;

		$this->body = "Dear Mr<br /> First name: {first_name}<br /> Last name: {last_name}";
		$this->bodyTranslated = "Translated Content Element:<br />" . $this->body;

		$this->layoutIdentifier = uniqid('identifier_');
		$this->layoutContent = "BEAUTIFUL LAYOUT HEADER<br/>{template}<br/>BEAUTIFUL LAYOUT FOOTER";
		$this->layoutContentTranslated = "Translated Content Element:<br />" . $this->layoutContent;

		// Populate the database with records
		$this->populateRecordTemplates();
		$this->populateRecordLayouts();
	}

		public function tearDown() {
			$this->testingFramework->cleanUp();
			unset($this->testingFramework);
	}

	/**
	 * @test
	 */
	public function frameWorkIsCorrectlyInstantiated() {
		$this->assertTrue($this->testingFramework instanceof Tx_Phpunit_Framework);
	}

	/**
	 * Populate DB with default records
	 */
	protected function populateRecordTemplates() {

		$uid = $this->testingFramework->createRecord(
			'tx_messenger_domain_model_messagetemplate',
			array(
				'identifier' => $this->identifier,
				'subject' => $this->subject,
				'body' => $this->body,
				'pid' => 1,
			)
		);

		$this->uidTemplate = $this->testingFramework->createRecord(
			'tx_messenger_domain_model_messagetemplate',
			array(
				'subject' => $this->subjectTranslated,
				'body' => $this->bodyTranslated,
				'sys_language_uid' => 1,
				'l10n_parent' => $uid,
				'pid' => 1,
			)
		);
	}

	/**
	 * Populate DB with default records
	 */
	protected function populateRecordLayouts() {

		$uid = $this->testingFramework->createRecord(
			'tx_messenger_domain_model_messagelayout',
			array(
				'identifier' => $this->layoutIdentifier,
				'content' => $this->layoutContent,
				'pid' => 1,
			)
		);

		$this->uidLayout = $this->testingFramework->createRecord(
			'tx_messenger_domain_model_messagelayout',
			array(
				'content' => $this->layoutContentTranslated,
				'sys_language_uid' => 1,
				'l10n_parent' => $uid,
				'pid' => 1,
			)
		);
	}
}