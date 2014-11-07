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
use Zend\Mail\Transport;
use Zend\Mime\Mime;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

/**
 * Mail service
 * 
 * @category    Application
 * @package     Service
 */
class Mail implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Mail transport
     *
     * @var mixed
     */
    protected $transport = null;

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
     * Get mail transport
     *
     * @return mixed
     * @throws Exception    When badly configured
     * @return Transport\TransportInterface
     */
    public function getTransport()
    {
        if ($this->transport)
            return $this->transport;

        $options = $this->getServiceLocator()->get('Config');
        if (!isset($options['mail']))
            throw new Exception("Set mail parameters in config file!");

        if ($options['mail']['transport'] == 'sendmail') {
            $transport = new Transport\Sendmail();
        } else if ($options['mail']['transport'] == 'smtp') {
            $cfg = new Transport\SmtpOptions();
            $cfg->setHost($options['mail']['host']);
            $cfg->setPort($options['mail']['port']);
            $transport = new Transport\Smtp($cfg);
        } else {
            throw new \Exception("Set mail transport type in config file!");
        }

        $this->transport = $transport;
        return $transport;
    }

    /**
     * Create UTF-8 HTML message
     *
     * @param string htmlBody
     * @return Message
     */
    public function createHtmlMessage($htmlBody)
    {
        $html = new MimePart($htmlBody);
        $html->type = "text/html; charset=UTF-8";
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new MimeMessage();
        $body->setParts(array($html));

        $msg = new Message();
        $msg->setBody($body);
        $msg->setEncoding('UTF-8');

        return $msg;
    }
}
