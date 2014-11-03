<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Http\Request as HttpRequest;

/**
 * Main module boostrap class
 * 
 * @category    Application
 * @package     Bootstrap
 */
class Module
{
    /**
     * Bootstrap code
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Initialize Content-Type header (we are adding charset)
        $response = $e->getResponse();
        if ($response instanceof \Zend\Http\Response) {
            $headers = $response->getHeaders();
            if ($headers)
                $headers->addHeaderLine('Content-Type', 'text/html; charset=utf-8');
        }
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Return an array for passing to Zend\Loader\AutoloaderFactory.
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Returns usage information for this module's Console commands.
     * 
     * @param AdapterInterface $console
     * @return  array
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'cron' => '',
            [ PHP_EOL, 'Run cron job' ],

            'populate-db' => '',
            [ PHP_EOL, 'Populate the database' ],
        );
    }
}
