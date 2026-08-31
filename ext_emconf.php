<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Fluid Components',
    'description' => 'Encapsulated frontend components with Fluid\'s ViewHelper syntax',
    'category' => 'fe',
    'author' => 'Ulrich Mathes, Simon Praetorius',
    'author_email' => 'mathes@sitegeist.de, moin@praetorius.me',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'stable',
    'version' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.3.99',
            'php' => '8.2.0-8.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'SMS\\FluidComponents\\' => 'Classes',
        ],
    ],
];
