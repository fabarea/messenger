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
 * Test case for class \Fab\Messenger\Validator\EmailValidator.
 */
class EmailValidatorTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \Fab\Messenger\Validator\EmailValidator
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new \Fab\Messenger\Validator\EmailValidator();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function validate() {

	}

	/**
	 * @test
	 * @dataProvider validEmailsProvider
	 */
	public function emailsAreCorrectlyValidated($emails) {
		$this->assertTrue($this->fixture->validate($emails));
	}

	/**
	 * @test
	 * @dataProvider invalidEmailsProvider
	 * @expectedException \Fab\Messenger\Exception\InvalidEmailFormatException
	 */
	public function wrongEmailsRaiseException($emails) {
		$this->fixture->validate($emails);
	}

	/**
	 * Valid emails provider
	 *
	 * @return array
	 */
	public function validEmailsProvider() {
		return array(
			array(array('john@doe.ch' => 'John Doe')),
			array(array('john@doe.bar.ch' => 'John Bar')),
		);
	}

	/**
	 * In-valid emails provider
	 *
	 * @return array
	 */
	public function invalidEmailsProvider() {
		return array(
			array(array('john@' => 'John Doe')),
			array(array('john@doe.ch' => '')),
			array(array('' => 'John Doe')),
			array(array('' => '')),
		);
	}
}
?>