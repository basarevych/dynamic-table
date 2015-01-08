<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Zend\Console\Adapter\Posix as PosixConsole;
use Zend\Mvc\MvcEvent;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Controller\ConsoleController;
use Application\Entity\Sample as SampleEntity;

class ConsoleControllerTest extends AbstractConsoleControllerTestCase
{
    protected $infrastructure;
    protected $repository;
    protected $em;

    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $moduleManager = $this->getApplicationServiceLocator()->get('ModuleManager');
        $moduleManager->loadModules();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Sample',
        ]);
        $this->repository = $this->infrastructure->getRepository('Application\Entity\Sample');
        $this->em = $this->infrastructure->getEntityManager();

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
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
        $this->dispatch('populate-db');

        $all = $this->repository->findAll();
        $this->assertGreaterThan(1, count($all), "No entities created");
    }
}
