<?php

namespace DynamicTableTest;

use PHPUnit_Framework_TestCase;
use DynamicTable\Table;
use DynamicTable\Adapter\DoctrineMongoODMAdapter;
use DynamicTableTest\Document\Sample as SampleDocument;

class OdmQueryMock {
    protected $cursor;

    public function __construct($cursor) {
        $this->cursor = $cursor;
    }

    public function execute() {
        return $this->cursor;
    }
}

class DoctrineMongoODMAdapterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getDocumentCollection', 'getClassMetadata' ])
                         ->getMock();

        $this->dm->expects($this->any())
                 ->method('getClassMetadata')
                 ->will($this->returnCallback(function ($name) {
                    return new \Doctrine\ODM\MongoDB\Mapping\ClassMetadata($name);
                 }));

        $this->qb = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
                         ->setConstructorArgs([ $this->dm, 'DynamicTableTest\Document\Sample' ])
                         ->setMethods([ 'expr', 'getQuery', 'sort' ])
                         ->getMock();

        $this->dm->expects($this->any())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($this->qb));

        $this->expr = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Expr')
                           ->setConstructorArgs([ $this->dm ])
                           ->setMethods([ 'getQuery', 'equals', 'range', 'gte', 'lte', 'exists' ])
                           ->getMock();

        $this->qb->expects($this->any())
                 ->method('expr')
                 ->will($this->returnValue($this->expr));

        $this->cursor = $this->getMockBuilder('Doctrine\ODM\MongoDB\Cursor')
                             ->disableOriginalConstructor()
                             ->setMethods([ 'valid', 'count', 'current', 'getMongoCursor', 'skip', 'limit' ])
                             ->getMock();

        $this->mongoCursor = $this->getMockBuilder('MongoCursor')
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->cursor->expects($this->any())
                     ->method('getMongoCursor')
                     ->will($this->returnValue($this->mongoCursor));

        $this->qb->expects($this->any())
                 ->method('getQuery')
                 ->will($this->returnValue(new OdmQueryMock($this->cursor)));

        $this->adapter = new DoctrineMongoODMAdapter();
        $this->adapter->setQueryBuilder($this->qb);

        $this->table = new Table();
        $this->table->setAdapter($this->adapter);
        $this->table->setColumns([
            'id' => [
                'title'         => 'ID',
                'field_name'    => 'id',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_EQUAL, Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'string' => [
                'title'         => 'String',
                'field_name'    => 'value_string',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_EQUAL, Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'integer' => [
                'title'         => 'Integer',
                'field_name'    => 'value_integer',
                'type'          => Table::TYPE_INTEGER,
                'filters'       => [ Table::FILTER_EQUAL, Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'float' => [
                'title'         => 'Float',
                'field_name'    => 'value_float',
                'type'          => Table::TYPE_FLOAT,
                'filters'       => [ Table::FILTER_EQUAL, Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'boolean' => [
                'title'         => 'Boolean',
                'field_name'    => 'value_boolean',
                'type'          => Table::TYPE_BOOLEAN,
                'filters'       => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'datetime' => [
                'title'         => 'DateTime',
                'field_name'    => 'value_datetime',
                'type'          => Table::TYPE_DATETIME,
                'filters'       => [ Table::FILTER_EQUAL, Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
        ]);
        $this->table->setMapper(function ($row) {
            $datetime = $row->getValueDatetime();
            if ($datetime !== null)
                $datetime = $datetime->getTimestamp();

            return [
                'id'        => $row->getId(),
                'string'    => $row->getValueString(),
                'integer'   => $row->getValueInteger(),
                'float'     => $row->getValueFloat(),
                'boolean'   => $row->getValueBoolean(),
                'datetime'  => $datetime,
            ];
        });
    }

    public function testCheck()
    {
        try {
            $this->adapter->check($this->table);
        } catch (\Exception $e) {
            $this->fail('check() failed on valid data');
        }
    }

    public function testSort()
    {
        $resultColumn = null;
        $resultDir = null;
        $this->qb->expects($this->any())
                 ->method('sort')
                 ->will($this->returnCallback(function ($column, $dir) use (&$resultColumn, &$resultDir) {
                    $resultColumn = $column;
                    $resultDir = $dir;
                }));

        $this->table->setSortColumn('id');
        $this->table->setSortDir(Table::DIR_ASC);

        $this->adapter->sort($this->table);

        $this->assertEquals('id', $resultColumn, "Incorrect sort column was set");
        $this->assertEquals(Table::DIR_ASC, $resultDir, "Incorrect sort direction was set");

        $this->table->setSortDir(Table::DIR_DESC);

        $this->adapter->sort($this->table);

        $this->assertEquals('id', $resultColumn, "Incorrect sort column was set");
        $this->assertEquals(Table::DIR_DESC, $resultDir, "Incorrect sort direction was set");
    }

    public function testFilter()
    {
        $this->table->setFilters([
            'string' => [
                Table::FILTER_EQUAL => 'foo',
                Table::FILTER_LIKE => 'bar',
                Table::FILTER_NULL => true
            ],
            'integer' => [
                Table::FILTER_EQUAL => "42",
                Table::FILTER_BETWEEN => [ "10", "20" ],
                Table::FILTER_NULL => true
            ],
            'boolean' => [
                Table::FILTER_EQUAL => false,
                Table::FILTER_NULL => true
            ],
            'datetime' => [
                Table::FILTER_EQUAL => 100000,
                Table::FILTER_BETWEEN => [ 1000000, 2000000 ],
                Table::FILTER_NULL => true
            ]
        ]);

        $this->adapter->filter($this->table);

        $reflection = new \ReflectionClass(get_class($this->adapter));
        $property = $reflection->getProperty('andOps');
        $property->setAccessible(true);
        $andOps = $property->getValue($this->adapter);

        $string = $andOps['value_string'];
        $this->assertEquals('equals', $string[0]['operator'], "Prepared andOps are invalid");
        $this->assertEquals('foo', $string[0]['value'], "Prepared andOps are invalid");
        $this->assertEquals('equals', $string[1]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(true, $string[1]['value'] instanceof \MongoRegex, "Prepared andOps are invalid");
        $this->assertEquals('exists', $string[2]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(false, $string[2]['value'], "Prepared andOps are invalid");

        $integer = $andOps['value_integer'];
        $this->assertEquals('equals', $integer[0]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(42, $integer[0]['value'], "Prepared andOps are invalid");
        $this->assertEquals('range', $integer[1]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(10, $integer[1]['value1'], "Prepared andOps are invalid");
        $this->assertEquals(20, $integer[1]['value2'], "Prepared andOps are invalid");
        $this->assertEquals('exists', $integer[2]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(false, $integer[2]['value'], "Prepared andOps are invalid");

        $boolean = $andOps['value_boolean'];
        $this->assertEquals('equals', $boolean[0]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(false, $boolean[0]['value'], "Prepared andOps are invalid");
        $this->assertEquals('exists', $boolean[1]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(false, $boolean[1]['value'], "Prepared andOps are invalid");

        $datetime = $andOps['value_datetime'];
        $this->assertEquals('equals', $datetime[0]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(true, $datetime[0]['value'] instanceof \MongoDate, "Prepared andOps are invalid");
        $this->assertEquals('range', $datetime[1]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(true, $datetime[1]['value1'] instanceof \MongoDate, "Prepared andOps are invalid");
        $this->assertEquals(true, $datetime[1]['value2'] instanceof \MongoDate, "Prepared andOps are invalid");
        $this->assertEquals('exists', $datetime[2]['operator'], "Prepared andOps are invalid");
        $this->assertEquals(false, $datetime[2]['value'], "Prepared andOps are invalid");
    }

    public function testPaginate()
    {
        $fixture = [];
        for ($i = 0; $i < 10; $i++) {
            $doc = new SampleDocument();
            $doc->setValueInteger(rand(0, 100));
            $fixture[] = $doc;
        }

        $i = 0; $count = count($fixture);
        $this->cursor->expects($this->any())
                     ->method('valid')
                     ->will($this->returnCallback(function () use (&$i, $count) {
                        return $i < $count;
                     }));

        $this->cursor->expects($this->any())
                     ->method('count')
                     ->will($this->returnValue($count));

        $this->cursor->expects($this->any())
                     ->method('current')
                     ->will($this->returnCallback(function () use (&$i, $fixture) {
                        return $fixture[$i++];
                     }));

        $passedSkip = null;
        $this->cursor->expects($this->any())
                     ->method('skip')
                     ->will($this->returnCallback(function ($skip) use (&$passedSkip) {
                        $passedSkip = $skip;
                     }));

        $passedLimit = null;
        $this->cursor->expects($this->any())
                     ->method('limit')
                     ->will($this->returnCallback(function ($limit) use (&$passedLimit) {
                        $passedLimit = $limit;
                     }));

        $this->table->setPageSize(2);
        $this->table->setPageNumber(3);

        $data = $this->adapter->paginate($this->table);

        $this->assertEquals(5, $this->table->getTotalPages(), "There should be 5 pages");
        $this->assertEquals(2 * 2, $passedSkip, "Number of skipped docs is wrong");
        $this->assertEquals(2, $passedLimit, "Limit of return docs is wrong");

        for ($i = 0; $i < count($fixture); $i++)
            $this->assertEquals($fixture[$i]->getValueInteger(), $data[$i]['integer'], "Returned data is wrong");
    }
}
