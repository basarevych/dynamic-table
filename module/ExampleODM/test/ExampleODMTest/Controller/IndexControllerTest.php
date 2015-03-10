<?php

namespace ExampleODMTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Application\Document\Sample as SampleDocument;

class OdmExecuteMock {
    public function count() {
        return 0;
    }
}
class OdmQueryMock {
    public function execute() {
        return new OdmExecuteMock();
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

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getRepository', 'persist', 'remove', 'flush', 'getDocumentCollection', 'getClassMetadata' ])
                         ->getMock();

        $this->repository = $this->getMockBuilder('Application\Document\SampleRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'findAll', 'find' ])
                                 ->getMock();

        $this->qb = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
                         ->setConstructorArgs([ $this->dm ])
                         ->setMethods([ 'getQuery' ])
                         ->getMock();

        $this->dm->expects($this->any())
                 ->method('getClassMetadata')
                 ->will($this->returnCallback(function ($name) {
                    return new \Doctrine\ODM\MongoDB\Mapping\ClassMetadata($name);
                 }));

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->repository));

        $this->dm->expects($this->any())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($this->qb));

        $this->qb->expects($this->any())
                 ->method('getQuery')
                 ->will($this->returnValue(new OdmQueryMock()));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);

        $this->a = new SampleDocument();
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
        $this->dispatch('/example-odm');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('exampleodm');
        $this->assertControllerName('exampleodm\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('example-odm');
    }

    public function testIndexActionDisplaysDocument()
    {
        \Locale::setDefault('en_US');
        $this->dispatch('/example-odm');

        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*string 1\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*9,000\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*0.42\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*true\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*printDateTime\(' . $this->a->getValueDatetime()->getTimestamp() . '\);\s*$/m');
    }

    public function testEditFormActionCanBeAccessed()
    {
        $this->dispatch('/example-odm/index/edit-form');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('exampleodm');
        $this->assertControllerName('exampleodm\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('example-odm');
    }

    public function testEditFormActionCreatesDocument()
    {
        $this->dispatch('/example-odm/index/edit-form');
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
        $this->dm->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $this->dispatch('/example-odm/index/edit-form', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertNotEquals(null, $persisted, "Entity was not created");
        $this->assertEquals("new string", $persisted->getValueString(), "String was not saved");
        $this->assertEquals(123, $persisted->getValueInteger(), "Integer was not saved");
        $this->assertEquals(45.6, $persisted->getValueFloat(), "Float was not saved");
        $this->assertEquals(false, $persisted->getValueBoolean(), "Boolean was not saved");
        $this->assertEquals($dt, $persisted->getValueDatetime(), "DateTime was not saved");
    }

    public function testEditFormActionModifiesDocument()
    {
        $this->dispatch('/example-odm/index/edit-form?id=' . $this->a->getId());
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
        $this->dm->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $this->dispatch('/example-odm/index/edit-form', HttpRequest::METHOD_POST, $postParams);
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
        $this->dispatch('/example-odm/index/delete-form?id=' . $this->a->getId());
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('exampleodm');
        $this->assertControllerName('exampleodm\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('example-odm');
    }

    public function testDeleteFormActionDeletesDocument()
    {
        $this->dispatch('/example-odm/index/delete-form?id=' . $this->a->getId());
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
        $this->dm->expects($this->any())
                 ->method('remove')
                 ->will($this->returnCallback(function ($entity) use (&$removed) {
                    $removed = $entity;
                 }));

        $this->dispatch('/example-odm/index/delete-form', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertNotEquals(null, $removed, "Entity was not removed");
        $this->assertEquals($this->a->getId(), $removed->getId(), "Wrong entity was removed");
    }
}
