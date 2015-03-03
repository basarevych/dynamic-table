<?php

namespace ExampleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Application\Entity\Sample as SampleEntity;

class QueryMock {
    public function getSingleScalarResult() {
        return 0;
    }
}

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    use \ApplicationTest\Controller\RegexAtLeastOnceTrait;

    public function setUp()
    {
        \Locale::setDefault('en_US');

        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getRepository', 'persist', 'remove', 'flush' ])
                         ->getMockForAbstractClass();

        $this->repository = $this->getMockBuilder('Application\Repository\Sample')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'findAll', 'find' ])
                                 ->getMock();

        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                         ->setConstructorArgs([ $this->em ])
                         ->setMethods([ 'getQuery' ])
                         ->getMock();

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->repository));

        $this->em->expects($this->any())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($this->qb));

        $this->qb->expects($this->any())
                 ->method('getQuery')
                 ->will($this->returnValue(new QueryMock()));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);

        $this->a = new SampleEntity();
        $this->a->setValueString('string 1');
        $this->a->setValueInteger(9000);
        $this->a->setValueFloat(0.42);
        $this->a->setValueBoolean(true);
        $this->a->setValueDatetime(new \DateTime('2010-10-10 20:00:00'));

        $reflection = new \ReflectionClass(get_class($this->a));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->a, 42);

        $this->repository->expects($this->any())
                         ->method('findAll')
                         ->will($this->returnValue([ $this->a ]));
        $this->repository->expects($this->any())
                         ->method('find')
                         ->will($this->returnValue($this->a));
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

    public function testIndexActionDisplaysEntity()
    {
        \Locale::setDefault('en_US');
        $this->dispatch('/example');

        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*string 1\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*9,000\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*0.42\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*true\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*printDateTime\(' . $this->a->getValueDatetime()->getTimestamp() . '\);\s*$/m');
        $this->assertQuery('button[onclick="editEntityForm(42)"]');
        $this->assertQuery('button[onclick="deleteEntityForm(42)"]');
    }

    public function testEditFormActionCanBeAccessed()
    {
        $this->dispatch('/example/index/edit-form');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('example');
        $this->assertControllerName('example\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('example');
    }

    public function testEditFormActionCreatesEntity()
    {
        $this->dispatch('/example/index/edit-form');
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $dt = new \DateTime();
        $postParams = [
            'security'  => $security,
            'string'    => "new string",
            'integer'   => 123,
            'float'     => 45.6,
            'boolean'   => 0,
            'datetime'  => $dt->format("Y-m-d H:i:s P")
        ];

        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $this->dispatch('/example/index/edit-form', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertNotEquals(null, $persisted, "Entity was not created");
        $this->assertEquals("new string", $persisted->getValueString(), "String was not saved");
        $this->assertEquals(123, $persisted->getValueInteger(), "Integer was not saved");
        $this->assertEquals(45.6, $persisted->getValueFloat(), "Float was not saved");
        $this->assertEquals(false, $persisted->getValueBoolean(), "Boolean was not saved");
        $this->assertEquals($dt, $persisted->getValueDatetime(), "DateTime was not saved");
    }

    public function testEditFormActionModifiesEntity()
    {
        $this->dispatch('/example/index/edit-form?id=' . $this->a->getId());
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $dt = new \DateTime();
        $postParams = [
            'security'  => $security,
            'id'        => $this->a->getId(),
            'string'    => "new string",
            'integer'   => 123,
            'float'     => 45.6,
            'boolean'   => 0,
            'datetime'  => $dt->format("Y-m-d H:i:s P")
        ];

        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $this->dispatch('/example/index/edit-form', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertNotEquals(null, $persisted, "Entity was not created");
        $this->assertEquals("new string", $persisted->getValueString(), "String was not saved");
        $this->assertEquals(123, $persisted->getValueInteger(), "Integer was not saved");
        $this->assertEquals(45.6, $persisted->getValueFloat(), "Float was not saved");
        $this->assertEquals(false, $persisted->getValueBoolean(), "Boolean was not saved");
        $this->assertEquals($dt, $persisted->getValueDatetime(), "DateTime was not saved");
    }

    public function testDeleteFormActionCanBeAccessed()
    {
        $this->dispatch('/example/index/delete-form?id=' . $this->a->getId());
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('example');
        $this->assertControllerName('example\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('example');
    }

    public function testDeleteFormActionDeletesEntity()
    {
        $this->dispatch('/example/index/delete-form?id=' . $this->a->getId());
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security'  => $security,
            'id'        => $this->a->getId(),
        ];

        $removed = null;
        $this->em->expects($this->any())
                 ->method('remove')
                 ->will($this->returnCallback(function ($entity) use (&$removed) {
                    $removed = $entity;
                 }));

        $this->dispatch('/example/index/delete-form', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertNotEquals(null, $removed, "Entity was not removed");
        $this->assertEquals($this->a->getId(), $removed->getId(), "Wrong entity was removed");
    }
}
