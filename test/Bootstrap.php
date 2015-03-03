<?php

chdir(dirname(__DIR__));

include __DIR__ . '/../init_autoloader.php';

$loader->add('Application', 'module/Application/src');
$loader->add('ApplicationTest', 'module/Application/test');
$loader->add('Example', 'module/Example/src');
$loader->add('ExampleTest', 'module/Example/test');
