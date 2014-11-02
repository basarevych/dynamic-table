<?php

return [
    'controllers' => [
        'invokables' => [
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Console' => 'Application\Controller\ConsoleController'
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',     // We can return JsonModel instead of ViewModel
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
        'locale' => 'en',
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
