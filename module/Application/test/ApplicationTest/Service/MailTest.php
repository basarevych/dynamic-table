<?php

namespace ApplicationTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mime\Message;
use Application\Service\Mail as MailService;

class MailTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->getApplication()->bootstrap();
    }

    public function testServiceLocatorMethods()
    {
        $service = new MailService();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals($sl, $service->getServiceLocator(), "Service Locator is wrong");
    }

    public function testGetSendmailTransport()
    {
        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);

        $config = $sl->get('Config');
        $config['mail'] = [
            'transport' => 'sendmail'
        ];
        $sl->setService('Config', $config);

        $service = new MailService();
        $service->setServiceLocator($sl);
        $transport = $service->getTransport();

        $this->assertEquals(true, $transport instanceof Sendmail);
    }

    public function testGetSmtpTransport()
    {
        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);

        $config = $sl->get('Config');
        $config['mail'] = [
            'transport' => 'smtp',
            'host'      => '127.0.0.1',
            'port'      => 25
        ];
        $sl->setService('Config', $config);

        $service = new MailService();
        $service->setServiceLocator($sl);
        $transport = $service->getTransport();

        $this->assertEquals(true, $transport instanceof Smtp);
    }

    public function testCreateHtmlMessage()
    {
        $sl = $this->getApplicationServiceLocator();
        $service = new MailService();
        $service->setServiceLocator($sl);

        $html = "<div>test</div>";
        $msg = $service->createHtmlMessage($html);
        $body = $msg->getBody();
        $parts = $body->getParts();

        $this->assertEquals('UTF-8', $msg->getEncoding(), "Encoding is not UTF-8");
        $this->assertEquals(true, $body instanceof Message, "Body is not MIME Message");
        $this->assertEquals(1, count($parts), "Message parts count is not 1");
        $this->assertEquals('text/html; charset=UTF-8', $parts[0]->type, "Message part has wrong type");
        $this->assertEquals($html, $parts[0]->getContent(), "Message content is wrong");
     }
}
