<?php

chdir(dirname(__DIR__));

include __DIR__ . '/../init_autoloader.php';

$loader->add('Application', 'module/Application/src');
$loader->add('ApplicationTest', 'module/Application/test');
$loader->add('ExampleORM', 'module/ExampleORM/src');
$loader->add('ExampleORMTest', 'module/ExampleORM/test');
$loader->add('ExampleODM', 'module/ExampleODM/src');
$loader->add('ExampleODMTest', 'module/ExampleODM/test');
