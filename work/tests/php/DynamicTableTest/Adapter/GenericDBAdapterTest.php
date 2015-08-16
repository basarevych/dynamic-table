<?php

namespace DynamicTableTest;

use PHPUnit_Framework_TestCase;
use DynamicTable\Table;
use DynamicTable\Adapter\GenericDBAdapter;

class GenericDBAdapterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->adapter = $this->getMockForAbstractClass('DynamicTable\Adapter\GenericDBAdapter');
        $this->adapter->setSelect('*');
        $this->adapter->setFrom('users');
        $this->adapter->setWhere("");
        $this->adapter->setParams([]);

        $this->table = new Table();
        $this->table->setAdapter($this->adapter);
        $this->table->setColumns([
            'id' => [
                'title' => 'ID',
                'sql_id' => 'id',
                'type' => Table::TYPE_INTEGER,
                'filters' => [ Table::FILTER_EQUAL ],
                'sortable' => true,
                'visible' => false,
            ],
            'string' => [
                'title' => 'String',
                'sql_id' => 'string',
                'type' => Table::TYPE_STRING,
                'filters' => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable' => true,
                'visible' => true,
            ],
            'integer' => [
                'title' => 'Integer',
                'sql_id' => 'integer',
                'type' => Table::TYPE_INTEGER,
                'filters' => [ Table::FILTER_BETWEEN ],
                'sortable' => true,
                'visible' => true,
            ],
            'float' => [
                'title' => 'Float',
                'sql_id' => 'float',
                'type' => Table::TYPE_FLOAT,
                'filters' => [ Table::FILTER_NULL ],
                'sortable' => true,
                'visible' => true,
            ],
            'boolean' => [
                'title' => 'Boolean',
                'sql_id' => 'boolean',
                'type' => Table::TYPE_BOOLEAN,
                'filters' => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
                'sortable' => true,
                'visible' => true,
            ],
            'datetime' => [
                'title' => 'DateTime',
                'sql_id' => 'datetime',
                'type' => Table::TYPE_DATETIME,
                'filters' => [ Table::FILTER_NULL ],
                'sortable' => true,
                'visible' => true,
            ],
        ]);
        $this->table->setMapper(function ($row) {
            $result = $row;

            if ($row['datetime'] !== null)
                $result['datetime'] = $row['datetime'].getTimestamp();

            return $result;
        });
    }

    public function testCheck()
    {
        $thrown = false;
        try {
            $this->adapter->check($this->table);
        } catch (\Exception $e) {
            $thrown = true;
        }
        $this->assertFalse($thrown, "Exception was thrown");
    }

    public function testSort()
    {
        $this->table->setSortColumn('boolean');
        $this->table->setSortDir(Table::DIR_ASC);

        $this->adapter->sort($this->table);
        $orderBy = $this->getPrivateProperty('DynamicTable\Adapter\GenericDBAdapter', 'sqlOrderBy');
        $this->assertEquals('boolean asc', $orderBy->getValue($this->adapter), "Wrong sort params");

        $this->table->setSortColumn('boolean');
        $this->table->setSortDir(Table::DIR_DESC);

        $this->adapter->sort($this->table);
        $orderBy = $this->getPrivateProperty('DynamicTable\Adapter\GenericDBAdapter', 'sqlOrderBy');
        $this->assertEquals('boolean desc', $orderBy->getValue($this->adapter), "Wrong sort params");
    }

    public function getPrivateProperty($className, $propertyName) {
        $reflector = new \ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);
 
        return $property;
    }
}
