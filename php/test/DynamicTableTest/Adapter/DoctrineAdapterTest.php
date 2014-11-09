<?php

namespace DynamicTableTest;

use PHPUnit_Framework_TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use DynamicTable\Table;
use DynamicTable\Adapter\DoctrineAdapter;
use DynamicTableTest\Entity\Sample as SampleEntity;

class DoctrineAdapterTest extends PHPUnit_Framework_TestCase
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

        $this->adapter = new DoctrineAdapter();
        $this->adapter->setMapper(function ($row) {
            $boolean = $row[0]->getValueBoolean();
            if ($boolean !== null)
                $boolean = $boolean ? 'TRUE' : 'FALSE';
            $datetime = $row[0]->getValueDatetime();
            if ($datetime !== null)
                $datetime = $datetime->format('Y-m-d H:i:s');

            return [
                'id'        => $row[0]->getId(),
                'string'    => $row[0]->getValueString(),
                'integer'   => $row[0]->getValueInteger(),
                'float'     => $row[0]->getValueFloat(),
                'boolean'   => $boolean,
                'datetime'  => $datetime,
                'computed'  => $row['computed'],
            ];
        });

        $this->table = new Table();
        $this->table->setAdapter($this->adapter);
        $this->table->setColumns([
            'id' => [
                'sql_id'    => 's.id',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_EQUAL ],
                'sortable'  => true,
            ],
            'string' => [
                'sql_id'    => 's.value_string',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'  => true,
            ],
            'integer' => [
                'sql_id'    => 's.value_integer',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_BETWEEN ],
                'sortable'  => true,
            ],
            'float' => [
                'sql_id'    => 's.value_float',
                'type'      => Table::TYPE_FLOAT,
                'filters'   => [ Table::FILTER_GREATER, Table::FILTER_LESS, Table::FILTER_NULL ],
                'sortable'  => true,
            ],
            'boolean' => [
                'sql_id'    => 's.value_boolean',
                'type'      => Table::TYPE_BOOLEAN,
                'filters'   => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
                'sortable'  => true,
            ],
            'datetime' => [
                'sql_id'    => 's.value_datetime',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_GREATER, Table::FILTER_LESS, Table::FILTER_NULL ],
                'sortable'  => true,
            ],
            'computed' => [
                'sql_id'    => 'computed',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_GREATER, Table::FILTER_LESS, Table::FILTER_NULL ],
                'sortable'  => true,
            ],
        ]);
    }

    public function testSortData()
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
        $this->adapter->sortData($this->table);

        $this->assertEquals('s.id', $resultColumn, "Incorrect sort column was set");
        $this->assertEquals(Table::DIR_ASC, $resultDir, "Incorrect sort direction was set");

        $this->table->setSortDir(Table::DIR_DESC);

        $this->adapter->sortData($this->table);

        $this->assertEquals('s.id', $resultColumn, "Incorrect sort column was set");
        $this->assertEquals(Table::DIR_DESC, $resultDir, "Incorrect sort direction was set");
    }

    public function testFilterData()
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
                Table::FILTER_BETWEEN => [ 10, 20 ]
            ],
            'float' => [
                Table::FILTER_GREATER => 5,
                Table::FILTER_LESS => 8
            ],
            'boolean' => [
                Table::FILTER_NULL => true
            ]
        ]);

        $this->adapter->setQueryBuilder($qb);
        $this->adapter->filterData($this->table);

        $this->assertEquals(
            "(s.id = :s_id_equal)"
            ." OR (s.value_string LIKE :s_value_string_like)"
            ." OR (s.value_integer > :s_value_integer_begin AND s.value_integer < :s_value_integer_end)"
            ." OR (s.value_float > :s_value_float_greater)"
            ." OR (s.value_float < :s_value_float_less)"
            ." OR (s.value_boolean IS NULL)",
            $resultWhere,
            "SQL WHERE is incorrect"
        );
        $this->assertEquals(
            [
                "s_id_equal" => 123,
                "s_value_string_like" => "%abc%",
                "s_value_integer_begin" => 10,
                "s_value_integer_end" => 20,
                "s_value_float_greater" => 5,
                "s_value_float_less" => 8
            ],
            $resultParams,
            "SQL parameters are wrong"
        );
    }

    public function testGetData()
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

        $data = $this->adapter->getData($this->table);

        $this->assertEquals(5, $this->table->getTotalPages(), "There should be 5 pages");
        $this->assertEquals(2, count($data), "Only two rows should be returned");
        $this->assertEquals(3, $data[0]['integer'], "Wrong data returned");
        $this->assertEquals(4, $data[1]['integer'], "Wrong data returned");
    }
}
