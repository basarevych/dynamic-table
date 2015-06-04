<?php

return [
    'controllers' => [
        'invokables' => [
            'ExampleODM\Controller\Index' => 'ExampleODM\Controller\IndexController',
        ],
    ],

    'view_manager' => [
        'doctype' => 'HTML5',
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'router' => [
        'routes' => [
            'example-odm' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/example-odm[/:controller[/:action]]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'ExampleODM\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],

    'translator' => [
        'locales' => [ 'en' ],
        'default' => 'en',
        'translation_file_patterns' => [
            [
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../l10n',
                'pattern'  => '%s.php',
            ],
        ],
    ],
];
