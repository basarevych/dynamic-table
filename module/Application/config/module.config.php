<?php

return [
    'controllers' => [
        'invokables' => [
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Console' => 'Application\Controller\ConsoleController'
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
            'application' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/[:controller[/:action]]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'cron' => [
                    'options' => [
                        'route'    => 'cron',
                        'defaults' => [
                            'controller' => 'Application\Controller\Console',
                            'action'     => 'cron'
                        ]
                    ]
                ],
                'populate-db' => [
                    'options' => [
                        'route'    => 'populate-db',
                        'defaults' => [
                            'controller' => 'Application\Controller\Console',
                            'action'     => 'populate-db'
                        ]
                    ]
                ],
            ],
        ],
    ],

    'translator' => [
        'locales' => [ 'en_US', 'ru_RU' ],
        'default' => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../l10n',
                'pattern'  => '%s.php',
            ],
        ],
    ],

    'doctrine' => [
        'driver' => [
            'application_entity' => [
                 'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                 'paths' => [ __DIR__ . '/../src/Application/Entity' ],
            ],
            'orm_default' => [
                 'drivers' => [
                    'Application\Entity' => 'application_entity'
                 ]
            ]
        ]
    ],
];
