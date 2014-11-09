<?php

namespace DynamicTableTest\Doctrine;

use PHPUnit_Framework_TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use DynamicTable\Doctrine\DynamicTable;
use DynamicTable\Doctrine\Sorter;
use DynamicTableTest\Entity\Sample as SampleEntity;

class SorterTest extends PHPUnit_Framework_TestCase
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

        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'addOrderBy' ])
                         ->getMock();

        $this->table = new DynamicTable();
        $this->table->setQueryBuilder($this->qb);
        $this->table->setColumns([
            'id' => [
                'sql_id'    => 's.id',
                'type'      => DynamicTable::TYPE_INTEGER,
                'filters'   => [ DynamicTable::FILTER_EQUAL ],
                'sortable'  => true,
            ],
            'string' => [
                'sql_id'    => 's.value_string',
                'type'      => DynamicTable::TYPE_STRING,
                'filters'   => [ DynamicTable::FILTER_LIKE, DynamicTable::FILTER_NULL ],
                'sortable'  => true,
            ],
            'integer' => [
                'sql_id'    => 's.value_integer',
                'type'      => DynamicTable::TYPE_INTEGER,
                'filters'   => [ DynamicTable::FILTER_BETWEEN ],
                'sortable'  => true,
            ],
            'float' => [
                'sql_id'    => 's.value_float',
                'type'      => DynamicTable::TYPE_FLOAT,
                'filters'   => [ DynamicTable::FILTER_GREATER, DynamicTable::FILTER_LESS, DynamicTable::FILTER_NULL ],
                'sortable'  => true,
            ],
            'boolean' => [
                'sql_id'    => 's.value_boolean',
                'type'      => DynamicTable::TYPE_BOOLEAN,
                'filters'   => [ DynamicTable::FILTER_EQUAL, DynamicTable::FILTER_NULL ],
                'sortable'  => true,
            ],
            'datetime' => [
                'sql_id'    => 's.value_datetime',
                'type'      => DynamicTable::TYPE_DATETIME,
                'filters'   => [ DynamicTable::FILTER_GREATER, DynamicTable::FILTER_LESS, DynamicTable::FILTER_NULL ],
                'sortable'  => true,
            ],
            'computed' => [
                'sql_id'    => 'computed',
                'type'      => DynamicTable::TYPE_INTEGER,
                'filters'   => [ DynamicTable::FILTER_GREATER, DynamicTable::FILTER_LESS, DynamicTable::FILTER_NULL ],
                'sortable'  => true,
            ],
        ]);
        $this->table->setMapper(function ($row) {
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
    }

    public function testApply()
    {
        $column = null;
        $dir = null;
        $this->qb->expects($this->any())
            ->method('addOrderBy')
            ->will($this->returnCallback(function ($column, $dir) use (&$resultColumn, &$resultDir) {
                $resultColumn = $column;
                $resultDir = $dir;
            }));

        $this->table->setSortColumn('id');
        $this->table->setSortDir(DynamicTable::DIR_ASC);

        $sorter = new Sorter();
        $sorter->apply($this->table);

        $this->assertEquals('s.id', $resultColumn, "Incorrect sort column was set");
        $this->assertEquals(DynamicTable::DIR_ASC, $resultDir, "Incorrect sort direction was set");

        $this->table->setSortColumn('id');
        $this->table->setSortDir(DynamicTable::DIR_DESC);

        $sorter = new Sorter();
        $sorter->apply($this->table);

        $this->assertEquals('s.id', $resultColumn, "Incorrect sort column was set");
        $this->assertEquals(DynamicTable::DIR_DESC, $resultDir, "Incorrect sort direction was set");
    }
}
