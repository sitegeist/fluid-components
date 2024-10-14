<?php
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
            'typo3' => '12.4.0-13.9.99',
            'php' => '8.2.0-8.3.99',
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
