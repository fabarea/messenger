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

.. image:: https://raw.github.com/fabarea/messenger/master/Documentation/Screenshot.png

Project info and releases
=========================

.. Stable version:
.. http://typo3.org/extensions/repository/view/messenger (not yet released on the TER)

Development version:
https://github.com/fabarea/messenger.git

::

    git clone https://github.com/fabarea/messenger.git

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
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::messenger);

	/** @var \Fab\Messenger\Domain\Model\Message $message */
	$message = $objectManager->get(Fab\Messenger\Domain\Model\Message:class);

	# Minimum required to be set
	$message->setMessageTemplate($templateIdentifier)
		->setTo($recipients);

	# Additional setter
	$message->assign('foo', $bar)
		->setLanguage($languageIdentifier)
		->addAttachment($pathToFile)
		->setMessageLayout($layoutIdentifier);

	# Send the email...
	$isSent = $message->send();


Configuration
=============

Following configuration should be configured. The default sender name::

	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'John Doe';
	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'john@doe.com';

Whenever Application Context is in Development, there is the chance to define
default target recipients which is convenient for not leaking email.
**Tip**: install extension ``EXT:application_context_hints`` to display some hints in the BE
about the current application context along with some global variables.

::

	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['development']['recipients'] = 'fudriot@cobweb.ch';


Tool to send emails to Frontend Users
=====================================

When EXT:vidi is installed, Messenger extends the Frontend User module in the BE and make it possible to send bulk messages to a selection / group of users.

To things must be considered. First, consider adding and configuring a scheduler task as messages are put into a queue and are sent by patch.
There is BE module to see the state of the queue where you can supervise the list of emails being sent.

Secondly, you may want to configure the list of possible senders (the contact person displayed as "from"). They could be retrieved from three different sources

- The currently logged-in BE User if the email address is defined.
- The PHP global configuration `defaultMailFromName` and `defaultMailFromAddress`
- User TSConfig where


```
    options.messenger {

        senders {
            0 {
                name = My Name
                email = test@example.tld
            }
        }
    }
```

Message View Helper
===================

View Helper which are bundled with this extension. The first oen is to render a generic item from the array of markers::

	# The minimum declaration
	<m:widget.show item="markerName" dataType="tx_ext_foo"/>

	# Additional attributes
	<m:widget.show item="markerName" dataType="tx_ext_foo" exclude="{0: 'fieldName'}" displaySystemFields="true"/>

	{namespace m=Fab\Messenger\ViewHelpers}

Retrieve the body of the email being sent. Useful to display to the User a feedback message
after a form has been posted which is actually the same as of the email::

	<m:show.body key="{settings.messageTemplate}"/>

Fluid templates
===============

More of Fluid's power can be used if the template is stored in external files.
In such a case layouts can be used. They have to be stored in a folder called
"Layouts", placed in the same folder as the template itself.

For example, if the template is located at "EXT:foo/Resource/Private/Templates/Mail/Bar.html"
it may refer to layouts located in "EXT:foo/Resource/Private/Templates/Mail/Layouts".

Furthermore, it is possible to choose "Fluid only" as a templating engine when
defining a message template. In such a case the Markdown interpreter will not run.
This means that the Fluid template can be written more freely.

Queue
=====

Messenger has the feature to queue emails. This is required as soon as making mass-mailing.

::

	/** @var \Fab\Messenger\Domain\Model\Message $message */
	$message = $objectManager->get('Fab\Messenger\Domain\Model\Message');
	$message->
		... // same as in the example above
		->enqueue();


Scheduler tip
=============

When sending messages using Messenger within a Scheduler task, the base url must be transmitted **as environment variable "TYPO3_BASE_URL"**.
The reasons is to have Fluid having the good context so that the ViewHelpers work as they would be in the context of the Frontend::

	TYPO3_BASE_URL=http://www.domain.tld typo3/cli_dispatch.phpsh scheduler

Additionally, you can set Development context with the environment variable "TYPO3_CONTEXT". Example::

	TYPO3_CONTEXT=Development TYPO3_BASE_URL=http://www.domain.tld typo3/cli_dispatch.phpsh scheduler


Todo
====

Long term goals:

+ Provide default FE / BE Users provider in Vidi
+ The same message can be sent in various language

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
