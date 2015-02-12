<?php

namespace ApplicationTest\Validator;

use PHPUnit_Framework_TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Validator\EntityNotExists;
use ApplicationTest\Validator\EntityNotExistsEntity as Entity;

class EntityNotExistsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            'ApplicationTest\Validator\EntityNotExistsEntity',
        ]);
        $this->repository = $this->infrastructure->getRepository(
            'ApplicationTest\Validator\EntityNotExistsEntity'
        );
        $this->em = $this->infrastructure->getEntityManager();
    }

    public function testOnExistingEntity()
    {
        $a = new Entity();
        $a->setValue('foo');

        $this->infrastructure->import([ $a ]);

        $validator = new EntityNotExists([
            'entityManager' => $this->em,
            'entity'        => 'ApplicationTest\Validator\EntityNotExistsEntity',
            'property'      => 'value',
        ]);

        $result = $validator->isValid('foo');
        $this->assertEquals(false, $result, "Check existing entity");

        $validator = new EntityNotExists([
            'entityManager' => $this->em,
            'entity'        => 'ApplicationTest\Validator\EntityNotExistsEntity',
            'property'      => 'value',
            'ignoreId'      => 1
        ]);

        $result = $validator->isValid('foo');
        $this->assertEquals(true, $result, "Check always valid entity");
    }

    public function testOnNonExistingEntity()
    {
        $a = new Entity();
        $a->setValue('foo');

        $this->infrastructure->import([ $a ]);

        $validator = new EntityNotExists([
            'entityManager' => $this->em,
            'entity'        => 'ApplicationTest\Validator\EntityNotExistsEntity',
            'property'      => 'value',
        ]);

        $result = $validator->isValid('bar');
        $this->assertEquals(true, $result, "Check non-existing entity");
    }
}
