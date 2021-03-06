<?php

namespace DynamicTableTest;

use PHPUnit_Framework_TestCase;
use DynamicTable\Table;
use DynamicTable\Adapter\ArrayAdapter;

class ArrayAdapterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->adapter = new ArrayAdapter();

        $this->table = new Table();
        $this->table->setAdapter($this->adapter);
        $this->table->setColumns([
            'id' => [
                'title'     => 'ID',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_EQUAL ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'string' => [
                'title'     => 'String',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'integer' => [
                'title'     => 'Integer',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_BETWEEN ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'float' => [
                'title'     => 'Float',
                'type'      => Table::TYPE_FLOAT,
                'filters'   => [ Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'boolean' => [
                'title'     => 'Boolean',
                'type'      => Table::TYPE_BOOLEAN,
                'filters'   => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'datetime' => [
                'title'     => 'DateTime',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
        ]);
        $this->table->setMapper(function ($row) {
            $result = $row;

            if ($row['datetime'] !== null)
                $result['datetime'] = $row['datetime']->getTimestamp();

            return $result;
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
        $a = [
            'id' => 1,
            'string' => "string 1",
            'integer' => 1,
            'float' => 0.01,
            'boolean' => true,
            'datetime' => new \DateTime('2010-03-25 13:13:13'),
        ];
        $b = [
            'id' => 2,
            'string' => "string 2",
            'integer' => 2,
            'float' => 0.02,
            'boolean' => false,
            'datetime' => new \DateTime('2010-03-25 14:14:14'),
        ];
        $c = [
            'id' => 3,
            'string' => null,
            'integer' => null,
            'float' => null,
            'boolean' => null,
            'datetime' => null,
        ];
        $d = [
            'id' => 4,
            'string' => "string 4",
            'integer' => 4,
            'float' => 0.04,
            'boolean' => false,
            'datetime' => new \DateTime('2010-03-25 16:16:16'),
        ];

        $this->adapter->setData([ $a, $b, $c, $d ]);

        foreach (['string', 'integer', 'float', 'datetime'] as $type) {
            $this->table->setSortColumn($type);
            $this->table->setSortDir(Table::DIR_ASC);

            $this->adapter->sort($this->table);
            $result = $this->adapter->getData();
            $ids = array_map(function ($a) { return $a['id']; }, array_values($result));
            $this->assertEquals([ 3, 1, 2, 4 ], $ids, "Incorrect $type sort (ASC)");

            $this->table->setSortColumn($type);
            $this->table->setSortDir(Table::DIR_DESC);

            $this->adapter->sort($this->table);
            $result = $this->adapter->getData();
            $ids = array_map(function ($a) { return $a['id']; }, array_values($result));
            $this->assertEquals([ 4, 2, 1, 3 ], $ids, "Incorrect $type sort (DESC)");
        }

        $this->table->setSortColumn('boolean');
        $this->table->setSortDir(Table::DIR_ASC);

        $this->adapter->sort($this->table);
        $result = $this->adapter->getData();
        $ids = array_map(function ($a) { return $a['id']; }, array_values($result));
        $this->assertEquals([ 3, 2, 4, 1 ], $ids, "Incorrect boolean sort (ASC)");

        $this->table->setSortColumn('boolean');
        $this->table->setSortDir(Table::DIR_DESC);

        $this->adapter->sort($this->table);
        $result = $this->adapter->getData();
        $ids = array_map(function ($a) { return $a['id']; }, array_values($result));
        $this->assertEquals([ 1, 4, 2, 3 ], $ids, "Incorrect boolean sort (DESC)");
    }

    public function testFilter()
    {
        $a = [
            'id' => 1,
            'string' => "string 1",
            'integer' => 1,
            'float' => 0.01,
            'boolean' => true,
            'datetime' => new \DateTime('2010-03-25 13:13:13'),
        ];
        $b = [
            'id' => 2,
            'string' => "string 2",
            'integer' => 2,
            'float' => 0.02,
            'boolean' => false,
            'datetime' => new \DateTime('2010-03-25 14:14:14'),
        ];
        $c = [
            'id' => 3,
            'string' => null,
            'integer' => null,
            'float' => null,
            'boolean' => null,
            'datetime' => null,
        ];
        $d = [
            'id' => 4,
            'string' => "string 4",
            'integer' => 4,
            'float' => 0.04,
            'boolean' => false,
            'datetime' => new \DateTime('2010-03-25 16:16:16'),
        ];

        $this->table->setFilters([
            'string' => [
                Table::FILTER_LIKE => '2'
            ]
        ]);

        $this->adapter->setData([ $a, $b, $c, $d ]);
        $this->adapter->filter($this->table);
        $result = $this->adapter->getData();

        $this->assertEquals(1, count($result), "One row should remain");
        $this->assertEquals(2, $result[0]['id'], "Incorrect row after LIKE filtering");

        $this->table->setFilters([
            'boolean' => [
                Table::FILTER_EQUAL => true
            ]
        ]);

        $this->adapter->setData([ $a, $b, $c, $d ]);
        $this->adapter->filter($this->table);
        $result = $this->adapter->getData();

        $this->assertEquals(1, count($result), "One row should remain");
        $this->assertEquals(1, $result[0]['id'], "Incorrect row after EQUAL filtering");

        $this->table->setFilters([
            'integer' => [
                Table::FILTER_BETWEEN => [1, 3]
            ]
        ]);

        $this->adapter->setData([ $a, $b, $c, $d ]);
        $this->adapter->filter($this->table);
        $result = $this->adapter->getData();

        $this->assertEquals(2, count($result), "Two rows should remain");
        $this->assertEquals(1, $result[0]['id'], "Incorrect row[0] after BETWEEN filtering");
        $this->assertEquals(2, $result[1]['id'], "Incorrect row[1] after BETWEEN filtering");

        $this->table->setFilters([
            'datetime' => [
                Table::FILTER_NULL => true,
            ]
        ]);

        $this->adapter->setData([ $a, $b, $c, $d ]);
        $this->adapter->filter($this->table);
        $result = $this->adapter->getData();

        $this->assertEquals(1, count($result), "One row should remain");
        $this->assertEquals(3, $result[0]['id'], "Incorrect row after NULL filtering");
    }

    public function testPaginate()
    {
        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'id' => $i,
                'string' => "string $i",
                'integer' => $i,
                'float' => $i / 100,
                'boolean' => ($i % 2 == 0),
                'datetime' => null
            ];
        }

        $this->adapter->setData($data);

        $this->table->setPageSize(2);
        $this->table->setPageNumber(2);

        $data = $this->adapter->paginate($this->table);

        $this->assertEquals(5, $this->table->getTotalPages(), "There should be 5 pages");
        $this->assertEquals(2, count($data), "Only two rows should be returned");
        $this->assertEquals(3, $data[0]['integer'], "Wrong data returned");
        $this->assertEquals(4, $data[1]['integer'], "Wrong data returned");
    }
}
