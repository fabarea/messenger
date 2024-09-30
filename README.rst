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
* Messages can be queued and scheduled for mass mailing. In this case, emails are sent via a scheduler task or a CLI command.

.. image:: https://raw.github.com/fabarea/messenger/master/Documentation/Screenshot.png

Project info and releases
=========================

.. Stable version:
.. http://typo3.org/extensions/repository/view/messenger (not yet released on the TER)

Development version:
https://github.com/fabarea/messenger.git

::

    composer require fab/messenger

Flash info about latest development or release
http://twitter.com/fudriot

Installation
============

Extension have self-explanatory settings in the Extension Manager. For a particular context you could configure
to have all emails redirected to a configured email for testing purposes and for not "leaking" messages outside
your dev environment.


Message composing
=================

When composing content, you can take full advantage of the Fluid syntax and make use of View Helper within your messages.
Markers should be defined as follows `{first_name}` and will be processed when rendering the email.

Note, you can use a double curly bracket `{{text}}` to have the marker interpreted as HTML. This would be the equivalent to
`<f:format.raw>{text}</f:format.raw>` in Fluid.


You may want to add prefix to all URLs with a domain (absolute URLs) which is required to have the links clickable.
Otherwise, links will be relative to nothing and this will simply not work correctly for the end user.

```
config.absRefPrefix = https://domain.tld/
```

Retrieve sent messages
======================

It could be handy to show sent messages or messages from the queue on the FE. We can achieve that knowing the UUID of the message.
In newsletter we often have see links like ""

```

# Display a sent message
https://domain.tld/?type=1556100596&uuid=a7760851-2349-4b5c-bc9e-ae43eecc01a9

# Display a message to be sent in the queue
https://domain.tld/?type=1556100596&uuid=a7760851-2349-4b5c-bc9e-ae43eecc01a9&source=queue
```

This can be used in a HTML content element in TYPO3 to generate a link to show the content that was sent to the User in the browser.

```
If this email is not shown correctly <a href="https://domain.tld?type=1556100596&uuid={uuid}">click here</a>.
```


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

	/** @var \Fab\Messenger\Domain\Model\Message $message */
	$message = GeneralUtility::makeInstance(Fab\Messenger\Domain\Model\Message:class);

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


Queue
=====

Messenger has the feature to queue up emails. This is advised as soon as sending many emails at once.

::

	/** @var \Fab\Messenger\Domain\Model\Message $message */
	$message = GeneralUtility::makeInstance('Fab\Messenger\Domain\Model\Message');
	$message->
		... // same as in the example above
		->enqueue();

Configuration
=============

Following configuration should be configured. The default sender name::

	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'John Doe';
	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'john@doe.com';

Whenever Application Context is in Development, there is the possibility to define
a default target recipient which is convenient for sending unwanted emails to real people.

::

	$GLOBALS['TYPO3_CONF_VARS']['MAIL']['development']['recipients'] = 'fabien@omic.ch';


Tool to send emails to Frontend Users
=====================================

Messenger makes it possible to send bulk messages to a selection / group of users.
There is BE module to see the state of the queue and the messages waiting to be sent.
Consider setting up the scheduler task to properly send the emails as messages are put into a queue and are sent by patch.

You have the possibility to configure a list of possible senders (the contact person displayed as "from"). They could be retrieved from three different sources

- The currently logged-in BE User if the email address is defined.
- The PHP global configuration `defaultMailFromName` and `defaultMailFromAddress`
- User TSConfig::

    options.messenger {

        senders {
            0 {
                name = My Name
                email = test@example.tld
            }
        }
    }

CLI
===

Messenger provides two commands.

Send messages and remove them from the queue by batch of 100 messages::

    ./vendor/bin/typo3 messenger:dequeue

Sent messages older than 100 days will be removed::

    ./vendor/bin/typo3 messenger:cleanUp


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

Sponsors
========

* `Ecodev`_ Ingénierie du développement durable – CMS – application web – bases de données – Webdesign
* `Gebrüderheitz`_ – Agentur für Webkommunikation
* `Cobweb`_ Agence web spécialisée dans le conseil web, le webdesign et la réalisation de sites internet

.. _Gebrüderheitz: https://gebruederheitz.de/
.. _Cobweb: https://www.cobweb.ch/
.. _Ecodev: https://www.ecodev.ch/
