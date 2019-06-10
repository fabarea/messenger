<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Send messages to a bunch of people',
    'description' => 'Send emails to a bunch of people. A message is wrapped in a template / layout that the User can freely edit in a BE module.',
    'category' => 'plugin',
    'author' => 'Fabien Udriot',
    'author_email' => 'fabien@ecodev.ch',
    'state' => 'beta',
    'version' => '2.2.0-dev',
    'autoload' => [
        'psr-4' => ['Fab\\Messenger\\' => 'Classes']
    ],
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
            'vidi' => '3.0.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
