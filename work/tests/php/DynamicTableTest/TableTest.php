<?php

namespace DynamicTableTest;

use PHPUnit_Framework_TestCase;
use DynamicTable\Table;

class TableTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->table = new Table();
    }

    public function testSetColumnsAcceptsValidData()
    {
        try {
            $this->table->setColumns([
                'id' => [
                    'title' => 'ID',
                    'type' => Table::TYPE_INTEGER,
                    'filters' => [ Table::FILTER_LIKE ],
                    'sortable' => true,
                    'visible' => true,
                ]
            ]);
        } catch (\Exception $e) {
            $this->fail('setColumns() failed on valid data');
        }
    }

    public function testSetFiltersCorrectsParams()
    {
        $this->table->setColumns([
            'id' => [
                'title' => 'ID',
                'type' => Table::TYPE_INTEGER,
                'filters' => [ Table::FILTER_EQUAL ],
                'sortable' => true,
                'visible' => true,
            ]
        ]);

        $this->table->setFilters([
            'id' => [
                Table::FILTER_EQUAL => 123,
                Table::FILTER_LIKE => 'xxx',
            ],
            'missing' => [
                Table::FILTER_EQUAL => 123,
            ]
        ]);

        $expected = [
            'id' => [
                Table::FILTER_EQUAL => 123,
            ],
        ];

        $this->assertEquals($expected, $this->table->getFilters());
    }

    public function testSetSortColumnCorrectsParams()
    {
        $this->table->setColumns([
            'id' => [
                'title' => 'ID',
                'type' => Table::TYPE_INTEGER,
                'filters' => [ Table::FILTER_EQUAL ],
                'sortable' => true,
                'visible' => true,
            ]
        ]);

        $this->table->setSortColumn('id');
        $this->assertEquals('id', $this->table->getSortColumn(), "Existing field could not be selected");

        $this->table->setSortColumn('missing');
        $this->assertEquals(null, $this->table->getSortColumn(), "Invalid field was not cleared");
    }

    public function testCalculatePageParams()
    {
        $this->table->setPageNumber(999);
        $this->table->setPageSize(12);

        $this->table->calculatePageParams(134);
        $total = $this->table->getTotalPages();
        $number = $this->table->getPageNumber();

        $this->assertEquals(ceil(134 / 12), $total, "'Total pages' is incorrect");
        $this->assertEquals($total, $number, "'Page number' was not set to 'total pages'");
    }

    public function testDescribe()
    {
       $columns = [ 
            'id' => [
                'title' => 'ID',
                'type' => Table::TYPE_INTEGER,
                'filters' => [ Table::FILTER_EQUAL ],
                'sortable' => true,
                'visible' => true,
            ]
        ];

        $this->table->setColumns($columns);
        $result = $this->table->describe();

        $this->assertEquals(true, is_array($result) && isset($result['columns']), "Invalid structure returned");
        $this->assertEquals($columns, $result['columns'], "Invalid data returned");
    }

    public function testFetch()
    {
        $adapter = $this->getMockBuilder('DynamicTable\Adapter\AbstractAdapter')
                        ->getMockForAbstractClass();

        $this->table->setAdapter($adapter);
        $data = $this->table->fetch();
        $keys = array_keys($data);

        $this->assertEquals(true, is_array($data), "Data should be array");
        $this->assertEquals(true, in_array('sort_column', $keys), "No 'sort_column'");
        $this->assertEquals(true, in_array('sort_dir', $keys), "No 'sort_dir'");
        $this->assertEquals(true, in_array('page_number', $keys), "No 'page_number'");
        $this->assertEquals(true, in_array('page_size', $keys), "No 'page_size'");
        $this->assertEquals(true, in_array('total_pages', $keys), "No 'total_pages'");
        $this->assertEquals(true, in_array('filters', $keys), "No 'filters'");
        $this->assertEquals(true, in_array('rows', $keys), "No 'rows'");
    }
}
