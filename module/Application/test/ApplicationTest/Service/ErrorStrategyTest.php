<?php

namespace ApplicationTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\ExceptionStrategy;
use Zend\Mvc\View\Http\RouteNotFoundStrategy;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Service\ErrorStrategy;
use Application\Exception\HttpException;

class ErrorStrategyTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->getApplication()->bootstrap();
    }

    public function testServiceLocatorMethods()
    {
        $service = new ErrorStrategy();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals(
            $sl,
            $service->getServiceLocator(),
            "Service Locator is wrong"
        );
    }

    public function testAttach()
    {
        $this->getApplication()->bootstrap();
        $serviceLocator = $this->getApplicationServiceLocator();
        $eventManager = $this->getApplication()->getEventManager();

        $service = $serviceLocator->get('ErrorStrategy');

        $defaultExceptionPresent = false;
        $defaultNotFoundPresent = false;

        $dispatchErrorAttached = false;
        $dispatchErrorPriority = 0;
        $dispatchErrorMaxPriority = 0;

        $renderErrorAttached = false;
        $renderErrorPriority = 0;
        $renderErrorMaxPriority = 0;

        foreach ($eventManager->getEvents() as $event) {
            foreach ($eventManager->getListeners($event) as $listener) {
                $metadata = $listener->getMetadata();
                $callback = $listener->getCallback();

                if ($callback[0] instanceof ExceptionStrategy)
                    $defaultExceptionPresent = true;
                else if ($callback[0] instanceof RouteNotFoundStrategy)
                    $defaultNotFoundPresent = true;

                if ($metadata['event'] == MvcEvent::EVENT_DISPATCH_ERROR) {
                    if ($callback[0] instanceof ErrorStrategy) {
                        $dispatchErrorAttached = true;
                        $dispatchErrorPriority = $metadata['priority'];
                    } else if ($dispatchErrorMaxPriority < $metadata['priority']) {
                        $dispatchErrorMaxPriority = $metadata['priority'];
                    }
                } else if ($metadata['event'] == MvcEvent::EVENT_RENDER_ERROR) {
                    if ($callback[0] instanceof ErrorStrategy) {
                        $renderErrorAttached = true;
                        $renderErrorPriority = $metadata['priority'];
                    } else if ($renderErrorMaxPriority < $metadata['priority']) {
                        $renderErrorMaxPriority = $metadata['priority'];
                    }
                }
            }
        }

        $sharedManager = $eventManager->getSharedManager();
        $id = 'Zend\Stdlib\DispatchableInterface';
        foreach ($sharedManager->getEvents($id) as $event) {
            foreach ($sharedManager->getListeners($id, $event) as $listener) {
                $metadata = $listener->getMetadata();
                $callback = $listener->getCallback();

                if ($callback[0] instanceof ExceptionStrategy)
                    $defaultExceptionPresent = true;
                else if ($callback[0] instanceof RouteNotFoundStrategy)
                    $defaultNotFoundPresent = true;
            }
        }

        $this->assertEquals(false, $defaultExceptionPresent, "Default exception strategy is not removed");
        $this->assertEquals(false, $defaultNotFoundPresent, "Default 404 strategy is not removed");

        $this->assertEquals(true, $dispatchErrorAttached, "ErrorStrategy is attached to EVENT_DISPATCH_ERROR");
        $this->assertGreaterThanOrEqual($dispatchErrorMaxPriority, $dispatchErrorPriority, "EVENT_DISPATCH_ERROR - not the highest priority");

        $this->assertEquals(true, $renderErrorAttached, "ErrorStrategy is attached to EVENT_RENDER_ERROR");
        $this->assertGreaterThanOrEqual($renderErrorMaxPriority, $renderErrorPriority, "EVENT_RENDER_ERROR - not the minimal priority");
    }

    public function testDetach()
    {
        $this->getApplication()->bootstrap();
        $serviceLocator = $this->getApplicationServiceLocator();
        $eventManager = $this->getApplication()->getEventManager();

        $service = $serviceLocator->get('ErrorStrategy');
        $service->attach($eventManager);
        $service->detach($eventManager);

        $dispatchErrorAttached = false;
        $renderErrorAttached = false;

        foreach ($eventManager->getEvents() as $event) {
            foreach ($eventManager->getListeners($event) as $listener) {
                $metadata = $listener->getMetadata();
                $callback = $listener->getCallback();

                if ($metadata['event'] == MvcEvent::EVENT_DISPATCH_ERROR
                        && $callback[0] instanceof ErrorStrategy) {
                    $dispatchErrorAttached = true;
                } else if ($metadata['event'] == MvcEvent::EVENT_RENDER_ERROR
                        && $callback[0] instanceof ErrorStrategy) {
                    $renderErrorAttached = true;
                }
            }
        }

        $this->assertEquals(false, $dispatchErrorAttached, "ErrorStrategy is still attached to EVENT_DISPATCH_ERROR");
        $this->assertEquals(false, $renderErrorAttached, "ErrorStrategy is still attached to EVENT_RENDER_ERROR");
    }

    public function testCheckErrorStatusCode()
    {
        $serviceLocator = $this->getApplicationServiceLocator();
        $service = $serviceLocator->get('ErrorStrategy');

        $request = new HttpRequest();
        $response = new HttpResponse();

        $e = new MvcEvent();
        $e->setRequest($request);
        $e->setResponse($response);

        $errors = [
            Application::ERROR_CONTROLLER_CANNOT_DISPATCH,
            Application::ERROR_CONTROLLER_NOT_FOUND,
            Application::ERROR_CONTROLLER_INVALID,
            Application::ERROR_ROUTER_NO_MATCH
        ];

        foreach ($errors as $error) {
            $e->setError($error);
            $service->checkError($e);
            $this->assertEquals(404, $response->getStatusCode(), "HTTP Error 404 is not set for $error");
        }

        $e->setError(Application::ERROR_EXCEPTION);
        $e->setParam('exception', new \Exception('Test exception', 999));
        $service->checkError($e);
        $this->assertEquals(500, $response->getStatusCode(), "HTTP Error 500 is not set for generic exception");

        $e->setParam('exception', new HttpException('Test exception', 400));
        $service->checkError($e);
        $this->assertEquals(400, $response->getStatusCode(), "Specific HTTP Error is not set for HttpException");
    }

    public function testCheckErrorForHtml()
    {
        $serviceLocator = $this->getApplicationServiceLocator();
        $service = $serviceLocator->get('ErrorStrategy');

        $request = new HttpRequest();
        $response = new HttpResponse();

        $headers = $request->getHeaders();
        $headers->addHeaderLine('Accept', 'text/html');

        $e = new MvcEvent();
        $e->setRequest($request);
        $e->setResponse($response);

        $e->setError(Application::ERROR_EXCEPTION);
        $e->setParam('exception', new \Exception('Test exception', 999));
        $service->checkError($e);
        $result = $e->getResult();

        $this->assertEquals(true, $result instanceof ViewModel, "ViewModel is not returned for Accept: html");
    }

    public function testCheckErrorForJson()
    {
        $serviceLocator = $this->getApplicationServiceLocator();
        $service = $serviceLocator->get('ErrorStrategy');

        $request = new HttpRequest();
        $response = new HttpResponse();

        $headers = $request->getHeaders();
        $headers->addHeaderLine('Accept', 'application/json');

        $e = new MvcEvent();
        $e->setRequest($request);
        $e->setResponse($response);

        $e->setError(Application::ERROR_EXCEPTION);
        $e->setParam('exception', new \Exception('Test exception', 999));
        $service->checkError($e);
        $result = $e->getResult();

        $this->assertEquals(true, $result instanceof JsonModel, "JsonModel is not returned for Accept: json");
    }
}
