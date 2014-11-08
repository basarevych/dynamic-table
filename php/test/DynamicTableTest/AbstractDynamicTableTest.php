<?php

namespace DynamicTableTest;

use PHPUnit_Framework_TestCase;
use DynamicTable\AbstractDynamicTable;

class AbstractDynamicTableTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->table = $this->getMockBuilder('DynamicTable\AbstractDynamicTable')
                            ->getMockForAbstractClass();
    }

    public function testSetColumnsAcceptsValidData()
    {
        try {
            $this->table->setColumns([
                'id' => [
                    'type' => AbstractDynamicTable::TYPE_INTEGER,
                    'filters' => [ AbstractDynamicTable::FILTER_LIKE ],
                    'sortable' => true,
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
                'type' => AbstractDynamicTable::TYPE_INTEGER,
                'filters' => [ AbstractDynamicTable::FILTER_EQUAL ],
                'sortable' => true,
            ]
        ]);

        $this->table->setFilters([
            'id' => [
                AbstractDynamicTable::FILTER_EQUAL => 123,
                AbstractDynamicTable::FILTER_LIKE => 'xxx',
            ],
            'missing' => [
                AbstractDynamicTable::FILTER_EQUAL => 123,
            ]
        ]);

        $expected = [
            'id' => [
                AbstractDynamicTable::FILTER_EQUAL => 123,
            ],
        ];

        $this->assertEquals($expected, $this->table->getFilters());
    }

    public function testSetFiltersJsonConvertsInput()
    {
        $this->table = $this->getMockBuilder('DynamicTable\AbstractDynamicTable')
                            ->setMethods([ 'setFilters' ])
                            ->getMockForAbstractClass();
        $this->table->expects($this->any())
            ->method('setFilters')
            ->will($this->returnCallback(function ($filters) use (&$result) {
                $result = $filters;
            }));


        $this->table->setColumns([
            'id' => [
                'type' => AbstractDynamicTable::TYPE_INTEGER,
                'filters' => [ AbstractDynamicTable::FILTER_EQUAL ],
                'sortable' => true,
            ]
        ]);

        $result = null;
        $this->table->setFiltersJson('{
            "id": {
                "equal": 123,
                "like": "xxx"
            },
            "missing": {
                "equal": 123
            }
        }');

        $expected = [
            'id' => [
                AbstractDynamicTable::FILTER_EQUAL => 123,
                AbstractDynamicTable::FILTER_LIKE => 'xxx',
            ],
            'missing' => [
                AbstractDynamicTable::FILTER_EQUAL => 123,
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testSetSortColumnCorrectsParams()
    {
        $this->table->setColumns([
            'id' => [
                'type' => AbstractDynamicTable::TYPE_INTEGER,
                'filters' => [ AbstractDynamicTable::FILTER_EQUAL ],
                'sortable' => true,
            ]
        ]);

        $this->table->setSortColumn('id');
        $this->assertEquals('id', $this->table->getSortColumn(), "Existing field could not be selected");

        $this->table->setSortColumn('missing');
        $this->assertEquals(null, $this->table->getSortColumn(), "Invalid field was not cleared");
    }

    public function testDescribe()
    {
       $columns = [ 
            'id' => [
                'type' => AbstractDynamicTable::TYPE_INTEGER,
                'filters' => [ AbstractDynamicTable::FILTER_EQUAL ],
                'sortable' => true,
            ]
        ];

        $this->table->setColumns($columns);
        $result = $this->table->describe();

        $this->assertEquals(true, is_array($result) && isset($result['columns']), "Invalid structure returned");
        $this->assertEquals($columns, $result['columns'], "Invalid data returned");
    }

}
