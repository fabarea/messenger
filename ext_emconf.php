<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Send messages to a bunch of people',
    'description' => 'Send emails to a bunch of people. A message is wrapped in a template / layout that the User can freely edit in a BE module.',
    'category' => 'plugin',
    'author' => 'Fabien Udriot',
    'author_email' => 'fabien@ecodev.ch',
    'state' => 'stable',
    'version' => '2.3.0-dev',
    'autoload' => [
        'psr-4' => ['Fab\\Messenger\\' => 'Classes']
    ],
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
            'vidi' => '4.0.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
