<?php

namespace ExampleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Dom\Document;
use PHPUnit_Framework_ExpectationFailedException;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Sample as SampleEntity;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    protected $infrastructure;
    protected $repository;
    protected $em;

    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Sample',
        ]);
        $this->repository = $this->infrastructure->getRepository('Application\Entity\Sample');
        $this->em = $this->infrastructure->getEntityManager();

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
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
        $dt = new \DateTime('2010-10-10 20:00:00');

        $a = new SampleEntity();
        $a->setValueString('string 1');
        $a->setValueInteger(9000);
        $a->setValueFloat(0.42);
        $a->setValueBoolean(true);
        $a->setValueDatetime($dt);

        \Locale::setDefault('en_US');
        $this->infrastructure->import([ $a ]);
        $this->dispatch('/example');

        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*string 1\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*9,000\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*0.42\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*true\s*$/m');
        $this->assertQueryContentRegexAtLeastOnce('table tr td', '/^\s*printDateTime\(' . $dt->getTimestamp() . '\);\s*$/m');
        $this->assertQuery('button[onclick="editEntityForm(1)"]');
        $this->assertQuery('button[onclick="deleteEntityForm(1)"]');
    }

    private function assertQueryContentRegexAtLeastOnce($path, $pattern, $useXpath = false)
    {
        $response = $this->getResponse();
        $document = new Document($response->getContent());

        if ($useXpath) {
            $document->registerXpathNamespaces($this->xpathNamespaces);
        }

        $result   = Document\Query::execute($path, $document, $useXpath ? Document\Query::TYPE_XPATH : Document\Query::TYPE_CSS);

        if ($result->count() == 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            ));
        }

        foreach ($result as $node) {
            if (preg_match($pattern, $node->nodeValue))
                return;
        }

        throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
            'Failed asserting node denoted by %s CONTAINS content MATCHING "%s"',
            $path,
            $pattern
        ));
    }
}
