<?php

namespace DynamicTableTest\Doctrine;

use PHPUnit_Framework_TestCase;
use DynamicTable\Doctrine\DynamicTable;

class DynamicTableTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->table = $this->getMockBuilder('DynamicTable\Doctrine\DynamicTable')
                            ->getMockForAbstractClass();
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
}
