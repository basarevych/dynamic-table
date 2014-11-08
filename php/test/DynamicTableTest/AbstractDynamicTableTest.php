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
}
