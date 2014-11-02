<?php

return [
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],

        'aliases' => [
            'translator' => 'MvcTranslator',
        ],
    ],

    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'types' => [
                    'utcdatetime' => 'Application\Doctrine\UtcDateTime'
                ]
            ]
        ],
    ],

    'connection' => [
        'orm_default' => [
            'doctrine_type_mappings' => [
                'utcdatetime' => 'utcdatetime'
            ],
        ]
    ],
];
