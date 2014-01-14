=====================
Messenger Extension
=====================

Messenger Extension is a TYPO3 extension for listing recipients in a flexible way and send them emails to them. A message (AKA email) is composed by a message template and
a possible wrapping layout. This can be useful if the message must be surrounded by a footer / header containing a logo or some default text.

.. image:: https://raw.github.com/gebruederheitz/messenger/master/Documentation/Screenshot.png

Development goes at https://github.com/gebruederheitz/messenger

Installation
==============

Extension have settings mainly in the Extension Manager. Most of them are self-explanatory.

The ``tableStructure`` setting will likely to be re-defined according to your need. See next chapter.

Multi-parted email
====================

Whenever possible, Messenger will send multi-parted email which contains a HTML version alongside to a plain text within the same email.

Message API
=================

Usage::

	$templateIdentifier = 1; // uid
	$layoutIdentifier = 1; // uid
	$recipients = array('john@doe.com' => 'John Doe');
	$markers = array(
	  'first_name' => 'John',
	  'last_name' => 'Doe',
	);
	$languageIdentifier = 0; // sys_language_uid
	$pathToFile = 'some-path-to-file'; // @todo replace me with FAL identifier

	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

	/** @var \TYPO3\CMS\Messenger\Domain\Model\Message $message */
	$message = $objectManager->get('TYPO3\CMS\Messenger\Domain\Model\Message');
	$message->setTemplate($templateIdentifier)
		->setRecipients($recipients)
		->setMarkers($markers)
		->setLanguage($languageIdentifier)
		->addAttachment($pathToFile)
		->setLayout($layoutIdentifier)
		->send();

There are two mandatory methods to set for sending a message::

	+ setRecipients()
	+ setTemplate() which can accept an object, a uid or an identifier property.

Notice the debug method. When set, the email will be sent to a debug email instead of the real one. This debug email address can be configured in file `ext_typoscript_setup.txt`::

Todo (long term)
=================

+ Improve message management in the BE module (create new one from scratch, edit, select, ...)
+ Provide default FE / BE Users provider
+ Add GUI to support layout wrapping
+ Add support for multi-language in the BE module
+ Add a possible "Mailing" Domain Model object for grouping sent emails
+ Add filtering capability to be provided by the list manager.
+ A message can be sent in various language.
+ Implement queue method part of the message API.

::

	$message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Messenger\Domain\Model\Message');
	$message->setIdentifier($templateIdentifier)
		->setRecipients($arrayOfRecipients)
		->setMarkers($arrayOfMarkers)
		->setSimulate($simulate)
		->setLanguage($languageUid)
		->addAttachment($pathToFile)
		->setLayout($layoutIdentifier)
		->queue();


List Manager
================

This paragraph is obsolete! The List Manager must be integrated in Vidi somehow.

In order to have a table of recipients displayed in the BE module a "list manager" must be provided where it is defined
what sort of data should be displayed. A list manager must implement a listable interface. As example,
a `Demo List Manager` is provided in the extension which can be taken as starting point for a custom implementation. The file is at
``\TYPO3\CMS\Messenger\ListManager\DemoListManager``

A list manager must be registered in ``ext_localconf.php`` as follows::

	# Register a new list manager for demo purposes.
	\TYPO3\CMS\Messenger\ListManager\Registry::getInstance()->add(

		# Corresponds to a class name.
		'TYPO3\CMS\Messenger\ListManager\DemoListManager',

		# A string or label describing the recipients (for the BE module needs).
		'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:demo_list_of_recipients'
	);

If more than two list managers are registered, a button is displayed in the BE module alongside the recipients heading, enabling a BE User
to pick between the managers. The choice is saved as preference per BE User.

Defining fields
-----------------

This paragraph is obsolete! The List Manager must be integrated in Vidi somehow.

Method ``getFields`` from the list manager must return an array with the following structure:

* fieldName - **mandatory** - the name of the property
* label - **mandatory** - the label of the property - example: LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:email,
* width - optional - a width for the column - "example: 30%"
* style - optional - a style for the column - "background-color: red"
* class - optional - class names for the column - "foo bar"

Note that the list manager is validate against a list manager validator.

Recipient
=========================

A recipient interface is provided making sure a user can be correctly displayed within the table. The interface is not mandatory to
be implemented since a recipient can be in the form of an array. However, a minimum of ``uid`` and ``email`` must be provided.
An exception will be raised on the run time if something goes wrong.

Sponsors
==============

* `Gebrüderheitz`_ – Agentur für Webkommunikation
* `Cobweb`_ Agence web spécialisée dans le conseil web, le webdesign et la réalisation de sites internet

.. _Gebrüderheitz: http://gebruederheitz.de/
.. _Cobweb: http://www.cobweb.ch/