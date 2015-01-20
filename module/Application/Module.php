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

        $serviceManager = $e->getApplication()->getServiceManager();
        $translator     = $serviceManager->get('translator');
        $session        = $serviceManager->get('Session');
        $config         = $serviceManager->get('Config');

        // Attach global strategies
        $serviceManager->get('ErrorStrategy')->attach($eventManager);

        // Initialize Content-Type header (we are adding charset)
        $response = $e->getResponse();
        if ($response instanceof \Zend\Http\Response) {
            $headers = $response->getHeaders();
            if ($headers)
                $headers->addHeaderLine('Content-Type', 'text/html; charset=utf-8');
        }

        // Initialize locale
        $locale = $this->getPreferredLocale($serviceManager);
        $fallback = $this->getDefaultLocale($serviceManager);
        if ($locale) {
            $translator->setLocale($locale);
            $translator->setFallbackLocale($fallback);
            locale_set_default($locale);
            setlocale(LC_ALL, $locale . '.UTF-8');
        }

        $cookie = null;
        $request = $serviceManager->get('Request');
        if ($request instanceof HttpRequest)
            $cookie = $request->getHeaders()->get('Cookie');

        $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
        $viewModel->locale = [
            'current'   => $locale,
            'cookie'    => ($cookie && $cookie->offsetExists('locale')) ? $cookie->locale : '',
            'available' => array_unique($config['translator']['locales']),
        ];

        // Start session
        $session->start();
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
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /**
     * Returns usage information for this module's Console commands.
     * 
     * @param AdapterInterface $console
     * @return  array
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            'cron' => '',
            [ PHP_EOL, 'Run cron job' ],

            'populate-db' => '',
            [ PHP_EOL, 'Populate the database' ],
        ];
    }

    /**
     * Returns defaut locale
     *
     * @param ServiceLocatorInterface $sl
     * @return string
     */
    protected function getDefaultLocale($sl)
    {
        $config = $sl->get('Config');
        if (!isset($config['translator']))
            throw new \Exception('No "translator" section in the config');
        if (!isset($config['translator']['default']))
            throw new \Exception('No "default" section in translator config');

        return $config['translator']['default'];
    }

    /**
     * Detects current locale using HTTP Accept-Language
     *
     * @param ServiceLocatorInterface $sl
     * @return string
     */
    protected function getPreferredLocale($sl)
    {
        $config = $sl->get('Config');
        if (!isset($config['translator']))
            throw new \Exception('No "translator" section in the config');
        if (!isset($config['translator']['locales']))
            throw new \Exception('No "locales" section in translator config');

        $supportedLocales = array_unique($config['translator']['locales']);

        $request = $sl->get('Request');
        if ($request instanceof HttpRequest) {
            $headers = $request->getHeaders();

            $cookie = $headers->get('Cookie');
            if ($cookie && $cookie->offsetExists('locale')) {
                $requested = $cookie->locale;
                if (in_array($requested, $supportedLocales))
                    return $requested;
            }

            $accept = $headers->get('Accept-Language');
            if ($accept) {
                $requested = $accept->getPrioritized();
                foreach ($requested as $item) {
                    $locale = \Locale::lookup($supportedLocales, $item->getLanguage());
                    if ($locale && in_array($locale, $supportedLocales))
                        return $locale;
                }
            }
        }

        return $this->getDefaultLocale($sl);
    }
}
