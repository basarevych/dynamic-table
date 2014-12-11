<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('application');
    }

    public function testDoctrineTableActionCanBeAccessed()
    {
        $this->dispatch('/index/doctrine-table?query=describe');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('application');

        $this->dispatch('/index/doctrine-table?query=data');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('application');
    }
}
