<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Zend\Console\Adapter\Posix as PosixConsole;
use Zend\Mvc\MvcEvent;
use Application\Controller\ConsoleController;
use Application\Entity\Sample as SampleEntity;

class ConsoleControllerTest extends AbstractConsoleControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $moduleManager = $this->getApplicationServiceLocator()->get('ModuleManager');
        $moduleManager->loadModules();

        $this->sampleRepository = $this->getMockBuilder('\Application\Repository\Sample')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
             ->disableOriginalConstructor()
             ->setMethods([ 'getRepository', 'persist', 'flush' ])
             ->getMockForAbstractClass();

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [ 'Application\Entity\Sample', $this->sampleRepository ],
            ]));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->entityManager);
    }

    public function testCronActionCanBeAccessed()
    {
        $this->dispatch('cron');
        $this->assertResponseStatusCode(0);

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\console');
        $this->assertControllerClass('ConsoleController');
        $this->assertMatchedRouteName('cron');
    }

    public function testPopulateDbActionCanBeAccessed()
    {
        $this->dispatch('populate-db');
        $this->assertResponseStatusCode(0);

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\console');
        $this->assertControllerClass('ConsoleController');
        $this->assertMatchedRouteName('populate-db');
    }

    public function testPopulateDbActionCreatesEntities()
    {
        $persisted = [];
        $this->entityManager->expects($this->any())
            ->method('persist')
            ->will($this->returnCallback(
                function ($param) use (&$persisted) { $persisted[] = $param; }
            ));

        $this->entityManager->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        $this->dispatch('populate-db');

        $entityCounter = 0;
        foreach ($persisted as $item) {
            if ($item instanceof SampleEntity) {
                $entityCounter++;
            }
        }

        $this->assertGreaterThan(1, $entityCounter);
    }

    public function testActionsFlush()
    {
        $this->entityManager->expects($this->any())
            ->method('persist')
            ->will($this->returnCallback(
                function ($param) use (&$needFlush) { $needFlush = true; }
            ));

        $this->entityManager->expects($this->any())
            ->method('flush')
            ->will($this->returnCallback(
                function ($param) use (&$needFlush) { $needFlush = false; }
            ));

        $needFlush = false;
        $this->dispatch('populate-db');
        $this->assertEquals(false, $needFlush, "populate-db is not flushing always");
    }
}
