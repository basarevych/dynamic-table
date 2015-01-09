<?php

namespace ApplicationTest\Repository;

use PHPUnit_Framework_TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Sample as SampleEntity;

class SampleTest extends PHPUnit_Framework_TestCase
{
    protected $infrastructure;
    protected $repository;
    protected $em;

    public function setUp()
    {
        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Sample',
        ]);
        $this->repository = $this->infrastructure->getRepository('Application\Entity\Sample');
        $this->em = $this->infrastructure->getEntityManager();
    }

    public function testRemoveAll()
    {
        $a = new SampleEntity();
        $a->setValueString('foo');

        $this->infrastructure->import([ $a ]);

        $this->repository->removeAll();

        $entities = $this->repository->findAll();
        $this->assertEquals(0, count($entities), "Some entities were not deleted");
    }
}
