=====================
Messenger Extension
=====================

Messenger Extension is a TYPO3 extension for listing recipients in a flexible way and send them emails to them. A message (AKA email) is composed by a message template and
a possible wrapping layout. This can be useful if the message must be surrounded by a footer / header containing a logo or some default text.

.. image:: https://raw.github.com/gebruederheitz/messenger/master/Documentation/Screenshot.png

Development goes at https://github.com/gebruederheitz/messenger

@todo publish fe_users_messenger
@todo publish be_users_messenger

Setting up
==============

Extension have settings mainly in the Extension Manager. Most of them are self-explanatory.
Though, **pay attention** to define a ``messageUid``. This can be achieved in the BE list view of TYPO3.

Web > List > Select a folder / page > Create new record (at the bottom) > Email template


Also the ``tableStructure`` setting will likely to be re-defined according to your need. See next chapter.


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

	$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_Manager');
	$message = $objectManager->get('Tx_Messenger_Domain_Model_Message');
	$message->setMessageTemplate($identifierString)
		->setRecipients($arrayOfRecipients)
		->setMarkers($arrayOfMarkers)
		->setLanguage($languageUid)
		->addAttachment($pathToFile)
		->setLayout($identifierString)
		->simulate()
		->send()


There are two mandatory methods to set for sending a message::

	+ setRecipients()
	+ setMessageTemplate() which can accept an object, a uid or an identifier property.

Notice the debug method. When set, the email will be sent to a debug email instead of the real one. This debug email address can be configured in file `ext_typoscript_setup.txt`::

Todo (long term)
=================

+ Add flash message when user interact with the GUI
+ Improve message management in the BE module (create new one from scratch, edit, select, ...)
+ Improve recipient management in the BE module (select new source)
+ Add GUI to support layout wrapping
+ Add support for multi-language in the BE module
+ Add a possible "Mailing" Domain Model object.
+ Add filtering capability to be provided by the list manager.
+ Add an option to load or not the BE module since the extension can be used as library for sending templated message.
+ A message can be sent in various language.
+ Add configuration for setting root pid to parse RTE configuration
  $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
+ Release side extensions for "fronted_user_messenger", "backend_user_messenger" and "web_page_messenger" which implement the list manager
+ Implement queue method part of the message API.

::

	$message = t3lib_div::makeInstance('Tx_Messenger_Domain_Model_Message')
	$message->setIdentifier($identifierString)
		->setRecipients($arrayOfRecipients)
		->setMarkers($arrayOfMarkers)
		->setSimulate(simulate)
		->setLanguage($languageUid)
		->addAttachment($pathToFile)
		->setLayout($identifierString)
		->queue();


Sponsors
==============

* `Gebrüderheitz`_ – Agentur für Webkommunikation
* `Cobweb`_ Agence web spécialisée dans le conseil web, le webdesign et la réalisation de sites internet

.. _Gebrüderheitz: http://gebruederheitz.de/
.. _Cobweb: http://www.cobweb.ch/