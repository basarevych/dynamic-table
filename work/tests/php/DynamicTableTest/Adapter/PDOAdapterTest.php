<?php

namespace DynamicTableTest;

use PHPUnit_Framework_TestCase;
use DynamicTable\Table;
use DynamicTable\Adapter\PDOAdapter;

class PDOAdapterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->adapter = new PDOAdapter();
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

            if (@$row['datetime'] !== null)
                $result['datetime'] = $row['datetime']->getTimestamp();

            return $result;
        });
    }

    public function testFilter()
    {
        $sqlAnds = $this->getPrivateProperty('DynamicTable\Adapter\PDOAdapter', 'sqlAnds');
        $sqlParams = $this->getPrivateProperty('DynamicTable\Adapter\PDOAdapter', 'sqlParams');

        $this->table->setFilters([
            'string' => [
                'like' => '2'
            ]
        ]);

        $this->adapter->filter($this->table);

        $this->assertEquals([ 'string' => [ 'string LIKE :dt_string_like' ] ], $sqlAnds->getValue($this->adapter), "LIKE expr check failed");
        $this->assertEquals([ ':dt_string_like' => '%2%' ], $sqlParams->getValue($this->adapter), "LIKE params check failed");

        $this->table->setFilters([
            'boolean' => [
                'equal' => true
            ]
        ]);

        $this->adapter->filter($this->table);

        $this->assertEquals([ 'boolean' => [ 'boolean = :dt_boolean_equal' ] ], $sqlAnds->getValue($this->adapter), "EQUAL expr check failed");
        $this->assertEquals([ ':dt_boolean_equal' => true ], $sqlParams->getValue($this->adapter), "EQUAL params check failed");

        $this->table->setFilters([
            'integer' => [
                'between' => [1, 3]
            ]
        ]);

        $this->adapter->filter($this->table);

        $this->assertEquals([ 'integer' => [ 'integer >= :dt_integer_begin AND integer <= :dt_integer_end' ] ], $sqlAnds->getValue($this->adapter), "BETWEEN expr check failed");
        $this->assertEquals([ ':dt_integer_begin' => 1, ':dt_integer_end' => 3 ], $sqlParams->getValue($this->adapter), "BETWEEN params check failed");

        $this->table->setFilters([
            'datetime' => [
                'null' => true,
            ]
        ]);

        $this->adapter->filter($this->table);

        $this->assertEquals([ 'datetime' => [ 'datetime IS NULL' ] ], $sqlAnds->getValue($this->adapter), "NULL expr check failed");
        $this->assertEquals([ ], $sqlParams->getValue($this->adapter), "NULL params check failed");
    }

    public function testPaginate()
    {
        $sth = $this->getMockBuilder('PDOStatement')
                    ->disableOriginalConstructor()
                    ->setMethods([ 'execute', 'fetchAll' ])
                    ->getMock();

        $executedParams = [];
        $sth->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function ($params) use (&$executedParams) {
                $executedParams[] = $params;
            }));

        $queryCounter = 0;
        $sth->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnCallback(function () use (&$queryCounter) {
                if ($queryCounter++)
                    return [ ];
                else
                    return [ [ 'count' => 6 ] ];
            }));

        $dbh = $this->getMockBuilder('PDO')
                    ->disableOriginalConstructor()
                    ->setMethods([ 'prepare' ])
                    ->getMock();

        $preparedSql = [];
        $dbh->expects($this->any())
            ->method('prepare')
            ->will($this->returnCallback(function ($sql) use (&$preparedSql, &$sth) {
                $preparedSql[] = preg_replace('/\s{2,}/', ' ', $sql);
                return $sth;
            }));

        $this->adapter->setPdo($dbh);

        $this->table->setPageSize(2);
        $this->table->setPageNumber(2);

        $this->adapter->paginate($this->table);

        $this->assertEquals($this->table->getTotalPages(), 3, "Wrong total pages");
        $this->assertEquals($preparedSql[0], "SELECT COUNT(*) AS count FROM users ", "Count query is wrong");
        $this->assertEquals($preparedSql[1], "SELECT * FROM users LIMIT 2 OFFSET 2 ", "Data query is wrong");
        $this->assertEquals($executedParams[0], [], "Count params are wrong");
        $this->assertEquals($executedParams[1], [], "Data params are wrong");
    }

    public function getPrivateProperty($className, $propertyName) {
        $reflector = new \ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);
 
        return $property;
    }
}
