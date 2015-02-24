<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\SaveHandler\Cache;

/**
 * Session service
 * 
 * @category    Application
 * @package     Service
 */
class Session implements ServiceLocatorAwareInterface
{
    /**
     * Was session started?
     *
     * @var boolean
     */
    protected $started = false;

    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Mail
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (!$this->serviceLocator)
            throw new \Exception('No Service Locator configured');
        return $this->serviceLocator;
    }

    /**
     * Configure the session and start
     */
    public function start()
    {
        $default = Container::getDefaultManager();
        if ($default->sessionExists())
            return;

        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        if (!isset($config['session']))
            throw new \Exception('No "session" section in the config');

        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);

        $sessionManager = new SessionManager($sessionConfig);
        switch ($config['session']['save_handler']) {
            case 'files':
                break;
            case 'memcached':
                $memcached = $sl->get('Memcached');
                $cache = new Cache($memcached);
                $sessionManager->setSaveHandler($cache);
                break;
            default:
                throw new \Exception('Unknown session save handler');
        }

        $sessionManager->start();
        Container::setDefaultManager($sessionManager);

        $init = $this->getContainer('session_initialized');
        if (!$init->offsetExists('session_initialized')) {
            $init->session_initialized = true;
            $sessionManager->regenerateId();
        }

        $this->started = true;
    }

    /**
     * Retrieves session container
     *
     * @param string $name
     * @return Container
     */
    public function getContainer($name = 'default')
    {
        if (!$this->started)
            $this->start();

        return new Container($name);
    }
}
