<?php

namespace DynamicTableTest;

use PHPUnit_Framework_TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use DynamicTable\Table;
use DynamicTable\Adapter\DoctrineORMAdapter;
use DynamicTableTest\Entity\Sample as SampleEntity;

class DoctrineORMAdapterTest extends PHPUnit_Framework_TestCase
{
    protected $infrastructure;
    protected $repository;
    protected $em;

    public function setUp()
    {
        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\DynamicTableTest\Entity\Sample',
        ]);
        $this->repository = $this->infrastructure->getRepository('DynamicTableTest\Entity\Sample');
        $this->em = $this->infrastructure->getEntityManager();

        $this->adapter = new DoctrineORMAdapter();

        $this->table = new Table();
        $this->table->setAdapter($this->adapter);
        $this->table->setColumns([
            'id' => [
                'title'     => 'ID',
                'sql_id'    => 's.id',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_EQUAL ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'string' => [
                'title'     => 'String',
                'sql_id'    => 's.value_string',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'integer' => [
                'title'     => 'Integer',
                'sql_id'    => 's.value_integer',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'float' => [
                'title'     => 'Float',
                'sql_id'    => 's.value_float',
                'type'      => Table::TYPE_FLOAT,
                'filters'   => [ Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'boolean' => [
                'title'     => 'Boolean',
                'sql_id'    => 's.value_boolean',
                'type'      => Table::TYPE_BOOLEAN,
                'filters'   => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'datetime' => [
                'title'     => 'DateTime',
                'sql_id'    => 's.value_datetime',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'computed' => [
                'title'     => 'Computed Value',
                'sql_id'    => 'computed',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
        ]);
        $this->table->setMapper(function ($row) {
            $datetime = $row[0]->getValueDatetime();
            if ($datetime !== null)
                $datetime = $datetime->getTimestamp();

            return [
                'id'        => $row[0]->getId(),
                'string'    => $row[0]->getValueString(),
                'integer'   => $row[0]->getValueInteger(),
                'float'     => $row[0]->getValueFloat(),
                'boolean'   => $row[0]->getValueBoolean(),
                'datetime'  => $datetime,
                'computed'  => $row['computed'],
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
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                   ->disableOriginalConstructor()
                   ->setMethods([ 'addOrderBy' ])
                   ->getMock();

        $resultColumn = null;
        $resultDir = null;
        $qb->expects($this->any())
            ->method('addOrderBy')
            ->will($this->returnCallback(function ($column, $dir) use (&$resultColumn, &$resultDir) {
                $resultColumn = $column;
                $resultDir = $dir;
            }));

        $this->table->setSortColumn('id');
        $this->table->setSortDir(Table::DIR_ASC);

        $this->adapter->setQueryBuilder($qb);
        $this->adapter->sort($this->table);

        $this->assertEquals('s.id', $resultColumn, "Incorrect sort column was set");
        $this->assertEquals(Table::DIR_ASC, $resultDir, "Incorrect sort direction was set");

        $this->table->setSortDir(Table::DIR_DESC);

        $this->adapter->sort($this->table);

        $this->assertEquals('s.id', $resultColumn, "Incorrect sort column was set");
        $this->assertEquals(Table::DIR_DESC, $resultDir, "Incorrect sort direction was set");
    }

    public function testFilter()
    {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                   ->disableOriginalConstructor()
                   ->setMethods([ 'andWhere', 'setParameter' ])
                   ->getMock();

        $resultWhere = null;
        $qb->expects($this->any())
            ->method('andWhere')
            ->will($this->returnCallback(function ($where) use (&$resultWhere) {
                $resultWhere = $where;
            }));

        $resultParams = [];
        $qb->expects($this->any())
            ->method('setParameter')
            ->will($this->returnCallback(function ($name, $value) use (&$resultParams) {
                $resultParams[$name] = $value;
            }));

        $this->table->setFilters([
            'id' => [
                Table::FILTER_EQUAL => 123
            ],
            'string' => [
                Table::FILTER_LIKE => 'abc'
            ],
            'integer' => [
                Table::FILTER_BETWEEN => [ 10, 20 ],
                Table::FILTER_NULL => true
            ],
        ]);

        $this->adapter->setQueryBuilder($qb);
        $this->adapter->filter($this->table);

        $this->assertEquals(
            "((s.id = :dt_s_id_equal))"
            ." AND ((s.value_string LIKE :dt_s_value_string_like))"
            ." AND ((s.value_integer >= :dt_s_value_integer_begin AND s.value_integer <= :dt_s_value_integer_end) OR (s.value_integer IS NULL))",
            $resultWhere,
            "SQL WHERE is incorrect"
        );
        $this->assertEquals(
            [
                "dt_s_id_equal" => 123,
                "dt_s_value_string_like" => "%abc%",
                "dt_s_value_integer_begin" => 10,
                "dt_s_value_integer_end" => 20,
            ],
            $resultParams,
            "SQL parameters are wrong"
        );
    }

    public function testPaginate()
    {
        $entities = [];
        for ($i = 1; $i <= 10; $i++) {
            $sample = new SampleEntity();
            $sample->setValueInteger($i);
            $entities[] = $sample;
        }

        $this->infrastructure->import($entities);

        $this->table->setPageSize(2);
        $this->table->setPageNumber(2);

        $qb = $this->em->createQueryBuilder();
        $qb->select('s, s.id + 100 AS computed')
           ->from('DynamicTableTest\Entity\Sample', 's');
        $this->adapter->setQueryBuilder($qb);

        $data = $this->adapter->paginate($this->table);

        $this->assertEquals(5, $this->table->getTotalPages(), "There should be 5 pages");
        $this->assertEquals(2, count($data), "Only two rows should be returned");
        $this->assertEquals(3, $data[0]['integer'], "Wrong data returned");
        $this->assertEquals(4, $data[1]['integer'], "Wrong data returned");
    }
}
