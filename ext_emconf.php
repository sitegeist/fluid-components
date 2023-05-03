<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Fluid Components',
    'description' => 'Encapsulated frontend components with Fluid\'s ViewHelper syntax',
    'category' => 'fe',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => false,
    'version' => '3.6.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.9.99',
            'php' => '7.4.0-8.9.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'SMS\\FluidComponents\\' => 'Classes'
        ]
    ],
];
