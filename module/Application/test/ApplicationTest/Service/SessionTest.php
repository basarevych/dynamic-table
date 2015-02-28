<?php

namespace ApplicationTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Zend\Session\Container;
use Application\Service\Session as SessionService;

class SessionTest extends AbstractControllerTestCase
{
    public function run(\PHPUnit_Framework_TestResult $result = null)
    {
        $this->setPreserveGlobalState(false);
        parent::run($result);
    }

    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->getApplication()->bootstrap();

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);

        $config = $sl->get('Config');
        $config['session'] = [
            'name'                => 'zf2skeleton',
            'save_handler'        => 'files', // 'files' or 'memcached'
//            'save_path'           => realpath(__DIR__ . '/../../data/session'),
            'remember_me_seconds' => 7 * 24 * 60 * 60,
            'cookie_lifetime'     => 7 * 24 * 60 * 60,
        ];
        $sl->setService('Config', $config);
    }

    public function testServiceLocatorMethods()
    {
        $service = new SessionService();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals(
            $sl,
            $service->getServiceLocator(),
            "Service Locator is wrong"
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionStarted()
    {
        $sl = $this->getApplicationServiceLocator();
        $config = $sl->get('Config');

        $manager = Container::getDefaultManager();
        $manager->destroy();

        $this->getApplication()->bootstrap();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status(), "Session is not started");
        $this->assertEquals($config['session']['name'], session_name(), "Session name is invalid");
    }

    /**
     * @runInSeparateProcess
     */
    public function testStartFiles()
    {
        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);

        $config = $sl->get('Config');
        $config['session'] = [
            'save_handler' => 'files',
        ];
        $sl->setService('Config', $config);

        $manager = Container::getDefaultManager();
        $manager->destroy();

        $service = new SessionService();
        $service->setServiceLocator($sl);
        $service->start();

        $manager = Container::getDefaultManager();
        $handler = $manager->getSaveHandler();

        $this->assertEquals(null, $handler);
    }

    /**
     * @runInSeparateProcess
     */
    public function testStartMemcached()
    {
        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);

        $config = $sl->get('Config');
        $config['session'] = [
            'save_handler' => 'memcached',
        ];
        $sl->setService('Config', $config);

        $memcached = $this->getMockBuilder('Zend\Cache\Storage\Adapter\Memcached')
            ->disableOriginalConstructor()
            ->getMock();
        $sl->setService('Memcached', $memcached);

        $manager = Container::getDefaultManager();
        $manager->destroy();

        $service = new SessionService();
        $service->setServiceLocator($sl);
        $service->start();

        $manager = Container::getDefaultManager();
        $handler = $manager->getSaveHandler();
        $storage = $handler->getCacheStorage();

        $this->assertEquals(true, $storage instanceof $memcached);
    }
}
