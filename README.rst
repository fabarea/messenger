===================
Messenger Extension
===================

Messenger Extension is a TYPO3 extension for listing recipients in a flexible way and send them emails to them. The extension basically contains:

* A email is composed by two parts: a message template and a possible layout. The layout will wrap the template.
  This can be useful if the message must be surrounded
  by a footer / header containing a logo or some default text.
* Messenger will send **multi-parted email** which contains a HTML
  version next to a plain text within the same email.
* Body message can be written in **Markdown syntax**
  alongside with **Fluid View Helper** which will be be rendered when sending the email.
* A message can be queued and scheduled for mass mailing

.. image:: https://raw.github.com/fudriot/messenger/master/Documentation/Screenshot.png

Project info and releases
=========================

.. Stable version:
.. http://typo3.org/extensions/repository/view/messenger (not yet released on the TER)

Development version:
https://github.com/fudriot/messenger.git

::

    git clone https://github.com/fudriot/messenger.git

Flash news about latest development or release
http://twitter.com/fudriot

Installation
============

Extension have settings mainly in the Extension Manager. Most of them are self-explanatory.

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
	$message->assign('foo', $bar)
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

View Helper which are bundled with this extension. The first oen is to render a generic item from the array of markers::

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