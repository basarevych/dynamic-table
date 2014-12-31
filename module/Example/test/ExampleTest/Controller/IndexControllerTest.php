<?php

namespace ExampleTest\Controller;

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
        $this->dispatch('/example');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('example');
        $this->assertControllerName('example\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('example');
    }
}
