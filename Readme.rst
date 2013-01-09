=====================
Messenger Extension
=====================

TYPO3 extension for listing recipients in a flexible way and send them messages (AKA emails). A message is composed by a message template and
a possible layout to wrap the template. This can be useful if the message must be wrapped with a footer / header containing logo, default text and so forth. A message can be translated in different language.

@todo add a little screenshot

Development goes at https://github.com/gebruederheitz/messenger

Configuration
==============

Extension have settings in the Extension Manager and also TypoScript.

For more details, check file ``ext_typoscript_setup.txt`` and ``ext_typoscript_constants.txt``.

List Manager
================

In order to have a table of recipients displayed in the BE module a "list manager" must be provided where it is defined
what sort of data should be displayed. A list manager must implement a listable interface. As example,
there is a Demo List Manager in the extension which can be taken as starting point in ``Tx_Messenger_ListManager_DemoListManager``.


Defining fields
-----------------

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


Message API
=================

Usage::


	$identifier = 'foo';
	$recipients = array('john@doe.com', 'John Doe');
	$markers = array(
	  'first_name' => 'John',
	  'last_name' => 'Doe',
	);

	$message = t3lib_div::makeInstance('Tx_Messenger_Domain_Model_Message')
	$message->setIdentifier($identifierString),
		->setRecipients($arrayOfRecipients),
		->setMarkers($arrayOfMarkers),
		->setDryRun($dryRunFlag),
		->setLanguage($languageUid),
		->addAttachment($pathToFile),
		->setLayout($identifierString),
		->send()


There are two mandatory methods to set for sending a message::

	+ setRecipients()
	+ setIdentifier() or setUid() one of them.

Notice the debug method. When set, the email will be sent to a debug email instead of the real one. This debug email address can be configured in file `ext_typoscript_setup.txt`::


Todo (long term)
=================

+ Improve message management in the BE module (create new one, pick existing, ...)
+ Add support for multi-language in the BE module
+ Add a possible "Mailing" Domain Model object.
+ Add filtering capability (to be provided by the list manager).
+ Add an option to load or not the BE module since the extension can be used as library for sending templated message.
+ Implement queue method part of the message API

::

	$message = t3lib_div::makeInstance('Tx_Messenger_Domain_Model_Message')
	$message->setIdentifier($identifierString),
		->setRecipients($arrayOfRecipients),
		->setMarkers($arrayOfMarkers),
		->setDryRun($dryRunFlag),
		->setLanguage($languageUid),
		->addAttachment($pathToFile),
		->setLayout($identifierString),
		->queue()