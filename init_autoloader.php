<?php
/**
 * zf2-doctrine2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-doctrine2-skeleton
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

// Composer autoloading
if (file_exists('vendor/autoload.php'))
    $loader = include 'vendor/autoload.php';

if (class_exists('Zend\Loader\AutoloaderFactory'))
    return;

$zf2Path = false;
if (is_dir('vendor/ZF2/library')) {
    $zf2Path = 'vendor/ZF2/library';
} elseif (getenv('ZF2_PATH')) {
    // Support for ZF2_PATH environment variable or git submodule
    $zf2Path = getenv('ZF2_PATH');
} elseif (get_cfg_var('zf2_path')) {
    // Support for zf2_path directive value
    $zf2Path = get_cfg_var('zf2_path');
}

if ($zf2Path) {
    if (isset($loader)) {
        $loader->add('Zend', $zf2Path);
        $loader->add('ZendXml', $zf2Path);
    } else {
        include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
        Zend\Loader\AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true
            )
        ));
    }
}

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    echo '<h1>An error occured</h1>';
    echo '<h3>Unable to load ZF2</h3>';
    echo 'Run `php composer.phar install` or define a ZF2_PATH environment variable.';
    die;
}
