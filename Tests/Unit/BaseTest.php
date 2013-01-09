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
 *  the Free Software Foundation; either version 3 of the License, or
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
 * Group together some utility for testing purposes
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @package TYPO3
 * @subpackage messenger
 * @author Fabien Udriot <fudriot@cobweb.ch>
 */
class Tx_Messenger_BaseTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

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

		$this->testingFramework->createRecord(
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

		$this->testingFramework->createRecord(
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
?>