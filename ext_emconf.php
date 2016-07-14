<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Send messages to a bunch of people',
	'description' => 'Send emails to a bunch of people. A message is wrapped in a template / layout that the User can freely edit in a BE module.',
	'category' => 'plugin',
	'author' => 'Fabien Udriot',
	'author_email' => 'fabien.udriot@typo3.org',
	'state' => 'beta',
	'version' => '0.9.0',
	'constraints' => [
		'depends' => [
			'vidi' => '',
			'typo3' => '6.2.0-7.6.99',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
