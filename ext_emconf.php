<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Fluid Components',
    'description' => 'Encapsulated frontend components with Fluid\'s ViewHelper syntax',
    'category' => 'fe',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'beta',
    'uploadfolder' => false,
    'clearCacheOnLoad' => false,
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.9.99',
            'php' => '7.2.0-7.9.99'
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
