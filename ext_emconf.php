<?php
$EM_CONF['fluid_components'] = [
    'title' => 'Fluid Components',
    'description' => 'Encapsulated frontend components with Fluid\'s ViewHelper syntax',
    'category' => 'fe',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => false,
    'version' => '2.5.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-11.9.99',
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
