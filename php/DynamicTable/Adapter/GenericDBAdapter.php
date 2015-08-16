<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable\Adapter;

use DynamicTable\Table;
use DynamicTable\Adapter\AbstractAdapter;

/**
 * Base class for DB adapter
 *
 * @category    DynamicTable
 * @package     Adapter
 */
abstract class GenericDBAdapter extends AbstractAdapter
{
    /**
     * Initial SELECT clause
     *
     * @var string
     */
    protected $initialSelect = "*";

    /**
     * Initial FROM clause
     *
     * @var string
     */
    protected $initialFrom = "";

    /**
     * Initial WHERE clause
     *
     * @var string
     */
    protected $initialWhere = "";

    /**
     * Initial bound parameters
     *
     * @var array
     */
    protected $initialParams = [];

    /**
     * SQL ANDs (will go to WHERE)
     *
     * Each item is an array of ORs, example:
     * [
     *   'id' => [
     *     'id > $1',   // or 'id > ?'
     *     'id IS NULL',
     *   ],
     * ]
     *
     * @var array
     */
    protected $sqlAnds = [];

    /**
     * Parameters of SQL where
     *
     * @var array
     */
    protected $sqlParams = [];

    /**
     * SQL order by clause
     *
     * @var string
     */
    protected $sqlOrderBy = "";

    /**
     * Set initial SELECT clause
     *
     * @param string $select
     * @return GenericDBAdapter
     */
    public function setSelect($select)
    {
        $this->initialSelect = $select;
        return $this;
    }

    /**
     * Get initial SELECT clause
     *
     * @return string
     */
    public function getSelect()
    {
        return $this->initialSelect;
    }

    /**
     * Set initial FROM clause
     *
     * @param string $from
     * @return GenericDBAdapter
     */
    public function setFrom($from)
    {
        $this->initialFrom = $from;
        return $this;
    }

    /**
     * Get initial FROM clause
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->initialFrom;
    }

    /**
     * Set initial WHERE clause
     *
     * @param string $where
     * @return GenericDBAdapter
     */
    public function setWhere($where)
    {
        $this->initialWhere = $where;
        return $this;
    }

    /**
     * Get initial WHERE clause
     *
     * @return string
     */
    public function getWhere()
    {
        return $this->initialWhere;
    }

    /**
     * Set initial parameters
     *
     * @param array $params
     * @return GenericDBAdapter
     */
    public function setParams($params)
    {
        $this->initialParams = $params;
        return $this;
    }

    /**
     * Get initial parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->initialParams;
    }

    /**
     * Check table and data
     *
     * @param Table $table
     * @throws Throws when error found
     * @return GenericDBAdapter
     */
    public function check(Table $table)
    {
        $columns = $table->getColumns();
        foreach ($columns as $id => $params) {
            if (!isset($params['sql_id']))
                throw new \Exception("No 'sql_id' param for ID $id");
        }

        $backupSqlAnds = $this->sqlAnds;
        $backupSqlParams = $this->sqlParams;

        $successfulFilters = [];
        foreach ($table->getFilters() as $column => $filterData) {
            $successfulNames = [];
            foreach ($filterData as $name => $value) {
                if ($this->buildFilter($columns[$column]['sql_id'], $columns[$column]['type'], $name, $value))
                    $successfulNames[$name] = $value;
            }
            if (count($successfulNames) > 0)
                $successfulFilters[$column] = $successfulNames;
        }
        $table->setFilters($successfulFilters);

        $this->sqlAnds = $backupSqlAnds;
        $this->sqlParams = $backupSqlParams;
        return $this;
    }

    /**
     * Filter data
     *
     * @param Table $table
     * @return GenericDBAdapter
     */
    public function filter(Table $table)
    {
        $this->sqlAnds = [];
        $this->sqlParams = $this->initialParams;

        $columns = $table->getColumns();
        foreach ($table->getFilters() as $column => $filterData) {
            foreach ($filterData as $name => $value)
                $this->buildFilter($columns[$column]['sql_id'], $columns[$column]['type'], $name, $value);
        }

        return $this;
    }

    /**
     * Sort data
     *
     * @param Table $table
     * @return GenericDBAdapter
     */
    public function sort(Table $table)
    {
        $column = $table->getSortColumn();
        $dir = $table->getSortDir();

        if (!$column)
            return;

        $sqlId = null;
        foreach ($table->getColumns() as $id => $params) {
            if ($id == $column) {
                $sqlId = $params['sql_id'];
                break;
            }
        }

        if (!$sqlId)
            throw new \Exception("No 'sql_id' for column: $column");

        $this->sqlOrderBy = $sqlId . " " . $dir;
        return $this;
    }

    /**
     * Build SQL query for a filter
     *
     * @param string field
     * @param string type
     * @param string filter
     * @param mixed value
     * @return boolean True on success
     */
    abstract protected function buildFilter($field, $type, $filter, $value);
}
