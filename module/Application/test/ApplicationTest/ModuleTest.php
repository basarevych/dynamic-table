<?php

namespace ApplicationTest;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Zend\Http\Response as HttpResponse;
use Zend\Http\Headers as HttpHeaders;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Session\Container;
use Application\Service\ErrorStrategy;

class ModuleTest extends AbstractControllerTestCase
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
    }

    public function testErrorStrategyAttached()
    {
        $service = $this->getMockBuilder('Application\Service\ErrorStrategy')
                        ->setMethods([ 'attach' ])
                        ->getMock();
        $service->expects($this->any())
            ->method('attach')
            ->will($this->returnCallback(function ($events) use (&$attachCalled) {
                $attachCalled = true;
            }));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('ErrorStrategy', $service);

        $attachCalled = false;
        $this->getApplication()->bootstrap();

        $this->assertEquals(true, $attachCalled);
    }

    public function testContentTypeHeaderIsCorrect()
    {
        $this->getApplication()->bootstrap();
        $sl = $this->getApplicationServiceLocator();

        $response = $sl->get('Response');
        if ($response instanceof HttpResponse) {
            $headers = $response->getHeaders();
            $this->assertEquals(true, $headers->has('Content-Type'), "Content-Type is not always set");
            $contentType = $headers->get('Content-Type');
            $this->assertEquals('utf-8', $contentType->getCharset(), "Content-Type is not with utf-8 charset");
        }
    }

    public function testPreferredLocaleSelected()
    {
        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);

        $config = $sl->get('Config');
        $config['translator'] = [
            'locales' => [ 'en', 'fr', 'ru' ],
            'default' => 'en',
        ];
        $sl->setService('Config', $config);

        $request = $sl->get('Request');
        if (! $request instanceof HttpRequest)
            return;

        $headers = $request->getHeaders();
        if (! $headers instanceof HttpHeaders)
            return;

        $headers->addHeaderLine('Accept-Language', 'en;q=0.1,fr;q=0.5,ru;q=0.3');
        $sl->setService('Request', $request);

        $this->getApplication()->bootstrap();

        $this->assertEquals('fr', locale_get_default());
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

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status(), "Session is not started");
        $this->assertEquals($config['session']['name'], session_name(), "Session name is invalid");
    }
}
