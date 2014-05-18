===================
Messenger Extension
===================

Messenger Extension is a TYPO3 extension for listing recipients in a flexible way and send them emails to them. A message (AKA email) is composed by a message template and
a possible wrapping layout. This can be useful if the message must be surrounded by a footer / header containing a logo or some default text.

.. image:: https://raw.github.com/fudriot/messenger/master/Documentation/Screenshot.png

Project info and releases
=========================

Stable version:
http://typo3.org/extensions/repository/view/messenger (not yet released on the TER)

Development version:
https://github.com/fudriot/messenger.git

::
    git clone https://github.com/fudriot/messenger.git

Flash news about latest development or release
http://twitter.com/fudriot


Installation
============

Extension have settings mainly in the Extension Manager. Most of them are self-explanatory.

The ``tableStructure`` setting will likely to be re-defined according to your need. See next chapter.

Multi-parted email
==================

Whenever possible, Messenger will send multi-parted email which contains a HTML version alongside to a plain text within the same email.

Message with Markdown and Fluid
===============================

Body message can be written using Markdown syntax alongside with Fluid View Helper which will be be rendered when sending the email.

Message API
===========

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

	/** @var \Vanilla\Messenger\Domain\Model\Message $message */
	$message = $objectManager->get('Vanilla\Messenger\Domain\Model\Message');

	# Minimum required to be set
	$message->setMessageTemplate($templateIdentifier)
		->setTo($recipients)

	# Additional setter
	$message->setMarkers($markers)
		->setLanguage($languageIdentifier)
		->addAttachment($pathToFile)
		->setMessageLayout($layoutIdentifier);

	# Possible debug before sending.
	# var_dump($message->toArray());

	# Send the email...
	$isSent = $message->send();


Notice the debug method. When set, the email will be sent to a debug email instead of the real one.
This debug email address can be configured in file `ext_typoscript_setup.txt`.


Message View Helper
===================

View Helper to render a generic item from the array of markers::

	# The minimum declaration
	<m:widget.show item="markerName" dataType="tx_ext_foo"/>

	# Additional attributes
	<m:widget.show item="markerName" dataType="tx_ext_foo" exclude="{0: 'fieldName'}" displaySystemFields="true"/>

	{namespace m=Vanilla\Messenger\ViewHelpers}

Retrieve the body of the email being sent. Useful to display to the User a feedback message
after a form has been posted which is actually the same as of the email::

	<m:show.body key="{settings.messageTemplate}"/>

Queue
=====

Alpha feature!

Messenger has the feature to queue emails. This is mandatory as soon as making mass-mailing.

@todo scheduler is not yet implemented.

::

	/** @var \Vanilla\Messenger\Domain\Model\Message $message */
	$message = $objectManager->get('Vanilla\Messenger\Domain\Model\Message');
	$message->
		... // same as in the example above
		->queue();

Todo
====

Long term goals:

+ Use Application Context as of TYPO3 6.2
+ Improve message management in the BE module (create new one from scratch, edit, select, ...)
+ Provide default FE / BE Users provider
+ Add GUI to support layout wrapping
+ A message can be sent in various language (alpha quality)

List Manager
============

This paragraph is obsolete! The List Manager must be integrated in Vidi somehow.

In order to have a table of recipients displayed in the BE module a "list manager" must be provided where it is defined
what sort of data should be displayed. A list manager must implement a listable interface. As example,
a `Demo List Manager` is provided in the extension which can be taken as starting point for a custom implementation. The file is at
``\Vanilla\Messenger\ListManager\DemoListManager``

A list manager must be registered in ``ext_localconf.php`` as follows::

	# Register a new list manager for demo purposes.
	\Vanilla\Messenger\ListManager\Registry::getInstance()->add(

		# Corresponds to a class name.
		'Vanilla\Messenger\ListManager\DemoListManager',

		# A string or label describing the recipients (for the BE module needs).
		'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:demo_list_of_recipients'
	);

If more than two list managers are registered, a button is displayed in the BE module alongside the recipients heading, enabling a BE User
to pick between the managers. The choice is saved as preference per BE User.

Defining fields
---------------

This paragraph is obsolete! The List Manager must be integrated in Vidi somehow.

Method ``getFields`` from the list manager must return an array with the following structure:

* fieldName - **mandatory** - the name of the property
* label - **mandatory** - the label of the property - example: LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:email,
* width - optional - a width for the column - "example: 30%"
* style - optional - a style for the column - "background-color: red"
* class - optional - class names for the column - "foo bar"

Note that the list manager is validate against a list manager validator.

Recipient Interface
===================

@todo check if that is still true with Vidi integration

A recipient interface is provided making sure a user can be correctly displayed within the table. The interface is not mandatory to
be implemented since a recipient can be in the form of an array. However, a minimum of ``uid`` and ``email`` must be provided.
An exception will be raised on the run time if something goes wrong.

Sponsors
========

* `Gebrüderheitz`_ – Agentur für Webkommunikation
* `Cobweb`_ Agence web spécialisée dans le conseil web, le webdesign et la réalisation de sites internet
* `Ecodev`_ Ingénierie du développement durable – CMS – application web – bases de données – Webdesign

.. _Gebrüderheitz: http://gebruederheitz.de/
.. _Cobweb: http://www.cobweb.ch/
.. _Ecodev: http://www.ecodev.ch/