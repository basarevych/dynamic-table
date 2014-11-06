<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Service;

use Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mail\Message;
use Zend\Stdlib\CallbackHandler;
use Application\Exception\HttpException;
use Application\Exception\NotFoundException;

/**
 * Generic error view strategy
 * 
 * @category    Application
 * @package     Service
 */
class ErrorStrategy implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Our listeners
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Should the exception details be provided to the user?
     *
     * @var boolean;
     */
    protected $displayExceptions = null;

    /**
     * Forward exception via email config
     *
     * @var array
     */
    protected $forward = [ 'enabled' => false ];

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Acl
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        if ($this->displayExceptions === null) {
            $config = $serviceLocator->get('config');
            if (!isset($config['exceptions']))
                die('No "exceptions" section in the config');
            if (!isset($config['exceptions']['display']))
                die('No "display" in "exceptions" section of the config');
            if (!isset($config['exceptions']['forward']) || !is_array($config['exceptions']['forward']))
                die('No "forward" array in "exceptions" section of the config');
            if (!isset($config['exceptions']['forward']['enabled']))
                die('No "enabled" in "exceptions/forward" section of the config');
            if (!isset($config['exceptions']['forward']['codes']) || !is_array($config['exceptions']['forward']['codes']))
                die('No "codes" array in "exceptions/forward" section of the config');
            if (!isset($config['exceptions']['forward']['from']))
                die('No "from" in "exceptions/forward" section of the config');
            if (!isset($config['exceptions']['forward']['to']))
                die('No "to" in "exceptions/forward" section of the config');
            if (!isset($config['exceptions']['forward']['subject']))
                die('No "subject" in "exceptions/forward" section of the config');

            $this->displayExceptions = ($config['exceptions']['display'] === true);
            $this->forward = $config['exceptions']['forward'];
        }

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
     * Attach the aggregate to the specified event manager
     *
     * @param EventManagerInterface $events
     * @return ErrorStrategy
     */
    public function attach(EventManagerInterface $events)
    {
        foreach ($events->getEvents() as $event) {
            foreach ($events->getListeners($event) as $listener) {
                $callback = $listener->getCallback();
                if ($callback[0] instanceof \Zend\Mvc\View\Http\ExceptionStrategy
                        || $callback[0] instanceof \Zend\Mvc\View\Http\RouteNotFoundStrategy)
                    $events->detach($listener);
            }
        }

        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            array($this, 'checkError'),
            99999
        );
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            array($this, 'checkError'),
            99999
        );

        return $this;
    }
    
    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return ErrorStrategy
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener))
                unset($this->listeners[$index]);
        }

        return $this;
    }

    /**
     * Check for errors
     *
     * @param MvcEvent $e
     */
    public function checkError(MvcEvent $e)
    {
        $error = $e->getError();
        if (empty($error))
            return;

        $request = $e->getRequest();
        $response = $e->getResponse();
        $ex = $e->getParam('exception');

        if (!$request instanceof HttpRequest)
            return;

        switch ($error) {
            case Application::ERROR_CONTROLLER_CANNOT_DISPATCH:
                $ex = new NotFoundException('The requested controller was unable to dispatch the request', 404, $ex);
                break;
            case Application::ERROR_CONTROLLER_NOT_FOUND:
                $ex = new NotFoundException('The requested controller could not be mapped to an existing controller class', 404, $ex);
                break;
            case Application::ERROR_CONTROLLER_INVALID:
                $ex = new NotFoundException('The requested controller was not dispatchable', 404, $ex);
                break;
            case Application::ERROR_ROUTER_NO_MATCH:
                $ex = new NotFoundException('The requested URL could not be matched by routing', 404, $ex);
                break;
        }

        if (! $ex instanceof \Exception)
            $ex = new HttpException('Exception parameter is not set. Error was: ' . $error, 500);

        if ($ex instanceof HttpException)
            $response->setStatusCode($ex->getCode());
        else
            $response->setStatusCode(500);

        $returnJson = false;
        $accept = $request->getHeader('Accept');
        if ($accept) {
            foreach ($accept->getPrioritized() as $type) {
                $string = $type->getTypeString();
                if ($string == 'text/html')
                    break;
                if ($string == 'application/json') {
                    $returnJson = true;
                    break;
                }
            }
        }

        if ($returnJson) {
            $data = array(
                'status' => $response->getStatusCode(),
                'title'  => $response->getReasonPhrase(),
            );
            if ($this->displayExceptions)
                $data['exception'] = $this->exceptionToArray($ex);
            $model = new JsonModel($data);
        } else {
            $model = new ViewModel(array(
                'status'            => $response->getStatusCode(),
                'title'             => $response->getReasonPhrase(),
                'exception'         => $ex,
                'displayExceptions' => $this->displayExceptions,
            ));
            $model->setTemplate('error/error');
        }

        $e->setResult($model);

        $sl = $this->getServiceLocator();
        if ($this->forward['enabled'] && $sl->has('Mail')
                && in_array($response->getStatusCode(), $this->forward['codes'])) {
            $body = "";
            $header = 'Exception information';
            do {
                $body .= "<h3>" . htmlentities($header, ENT_COMPAT, 'UTF-8') . "</h3>";
                $body .= "<h4>Class</h4>";
                $body .= "<pre>" . htmlentities(get_class($ex), ENT_COMPAT, 'UTF-8') . "</pre>";
                $body .= "<h4>Code / Message</h4>";
                $body .= "<pre>" . htmlentities($ex->getCode() . ' / ' . $ex->getMessage()) . "</pre>";
                $body .= "<h4>File / Line</h4>";
                $body .= "<pre>" . htmlentities($ex->getFile() . ': ' . $ex->getLine()) . "</pre>";
                $body .= "<h4>Stack trace</h4>";
                $body .= "<pre>" . htmlentities($ex->getTraceAsString()) . "</pre>";

                $ex = $ex->getPrevious();
                $header = 'Previous Exception information';
            } while ($ex);

            $mail = $sl->get('Mail');
            $msg = $mail->createHtmlMessage($body);
            $msg->setFrom($this->forward['from']);
            $msg->setTo($this->forward['to']);
            $msg->setSubject($this->forward['subject']);

            try {
                $mail->getTransport()->send($msg);
            } catch (\Exception $ex) {
                // Do nothing, no working mail service present
            }
        }
    }

    /**
     * Create array representation of an exception
     *
     * @param Exception $e
     * @return array
     */
    protected function exceptionToArray(Exception $e)
    {
        $data = array(
            'class'     => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => array()
        );

        $trace = $e->getTraceAsString();
        foreach (explode("\n", $trace) as $line)
            $data['trace'][] = $line;

        $prev = $e->getPrevious();
        if ($prev)
            $data['previous'] = $this->exceptionToArray($prev);

        return $data;
    }
}
