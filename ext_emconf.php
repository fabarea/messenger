<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Send messages to a bunch of people',
	'description' => 'Send emails to a bunch of people. A message is wrapped in a template / layout that the User can freely edit in a BE module.',
	'category' => 'plugin',
	'author' => 'Fabien Udriot',
	'author_email' => 'fabien@ecodev.ch',
	'state' => 'beta',
	'version' => '1.0.0-dev',
	'constraints' => [
		'depends' => [
			'typo3' => '7.6.0-8.7.99',
			'vidi' => '0.0.0-0.0.0',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
