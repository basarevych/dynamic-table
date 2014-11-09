<?php

namespace DynamicTableTest\Doctrine;

use PHPUnit_Framework_TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use DynamicTable\Doctrine\DynamicTable;
use DynamicTableTest\Entity\Sample as SampleEntity;

class DynamicTableTest extends PHPUnit_Framework_TestCase
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

        $qb = $this->em->createQueryBuilder();
        $qb->select('s, s.id + 100 AS computed')
           ->from('DynamicTableTest\Entity\Sample', 's');

        $this->table = new DynamicTable();
        $this->table->setQueryBuilder($qb);
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

    public function testSetColumnsAcceptsValidData()
    {
        try {
            $this->table->setColumns([
                'id' => [
                    'sql_id' => 'a.b',
                    'type' => DynamicTable::TYPE_INTEGER,
                    'filters' => [ DynamicTable::FILTER_LIKE ],
                    'sortable' => true,
                ]
            ]);
        } catch (\Exception $e) {
            $this->fail('setColumns() failed on valid data');
        }
    }

    public function testFetch()
    {
        $entities = [];
        for ($i = 1; $i <= 10; $i++) {
            $sample = new SampleEntity();
            $sample->setValueInteger($i);
            $entities[] = $sample;
        }

        $this->infrastructure->import($entities);

        $this->table->setFilters([
            'integer' => [
                DynamicTable::FILTER_BETWEEN => [ 2, 9 ],
            ]
        ]);
        $this->table->setSortColumn('integer');           
        $this->table->setSortDir(DynamicTable::DIR_DESC);
        $this->table->setPageSize(2);
        $this->table->setPageNumber(2);

        $data = $this->table->fetch();

        $this->assertEquals(true, is_array($data), "Data should be array");
        $this->assertEquals(true, isset($data['sort_column']), "No 'sort_column'");
        $this->assertEquals(true, isset($data['sort_dir']), "No 'sort_dir'");
        $this->assertEquals(true, isset($data['page_number']), "No 'page_number'");
        $this->assertEquals(true, isset($data['page_size']), "No 'page_size'");
        $this->assertEquals(true, isset($data['total_pages']), "No 'total_pages'");
        $this->assertEquals(true, isset($data['filters']), "No 'filters'");
        $this->assertEquals(true, isset($data['data']), "No 'data'");

        $this->assertEquals(3, $this->table->getTotalPages(), "There should be 3 pages");
        $this->assertEquals(2, count($data['data']), "Only two rows should be returned");
        $this->assertEquals(6, $data['data'][0]['integer'], "Wrong data returned");
        $this->assertEquals(5, $data['data'][1]['integer'], "Wrong data returned");
    }
}
