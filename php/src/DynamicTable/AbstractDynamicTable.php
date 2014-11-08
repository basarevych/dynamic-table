<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable;

/**
 * Base DynamicTable class
 *
 * @category    DynamicTable
 * @package     Base
 */
abstract class AbstractDynamicTable
{
    /**
     * Available column types
     *
     * @const TYPE_STRING
     * @const TYPE_INTEGER
     * @const TYPE_FLOAT
     * @const TYPE_BOOLEAN
     * @const TYPE_DATETIME
     */
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATETIME = 'datetime';

    /**
     * Available filters
     *
     * @const FILTER_LIKE
     * @const FILTER_EQUAL
     * @const FILTER_GREATER
     * @const FILTER_LESS
     * @const FILTER_NULL
     */
     const FILTER_LIKE = 'like';
     const FILTER_EQUAL = 'equal';
     const FILTER_GREATER = 'greater';
     const FILTER_LESS = 'less';
     const FILTER_NULL = 'null';

    /**
     * Sorting directions
     *
     * @const DIR_ASC
     * @const DIR_DESC
     */
    const DIR_ASC = 'asc';
    const DIR_DESC = 'desc';

    /**
     * Default page size
     *
     * @const PAGE_SIZE
     */
    const PAGE_SIZE = 15;

    /**
     * Table columns
     *
     * $columns = [
     *     $columnId => $columnParams,
     *     // ...
     * ];
     * $columnParams = [
     *     'type' => $columnType, // One of TYPE_* constants
     *     'filters' => $columnFilters, // Array of FILTER_* constants
     *     'sortable' => true|false,
     * ];
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Table data row -> result row mapper (optional)
     *
     * Function should get single argument $row, which is data row
     * and return an array which is feeded to the front-end
     *
     * @var \Closure
     */
    protected $mapper = null;

    /**
     * Query filters
     *
     * $filters = [
     *     $columnId => $filterParams,
     *     // ...
     * ];
     * $filterParams = [
     *     $filter => 'value', // $filter is one of FILTER_* constants
     *     // ...
     * ];
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Query sort column name
     *
     * @var string|null     No sorting when null
     */
    protected $sortColumn = null;

    /**
     * Query sort direction (asc|desc)
     *
     * @var string
     */
    protected $sortDir = self::DIR_ASC;

    /**
     * Number of current page
     *
     * @var integer
     */
    protected $pageNumber = 1;

    /**
     * Number of records per page. 0 = all records (one page)
     *
     * @var integer
     */
    protected $pageSize = self::PAGE_SIZE;

    /**
     * Total number of pages
     *
     * @var integer
     */
    protected $totalPages = 1;

    /**
     * Columns setter
     *
     * @param array $columns
     * @throws Exception            In case of invalid column definition
     * @return AbstractDynamicTable
     */
    public function setColumns(array $columns)
    {
        foreach ($columns as $id => $params) {
            if (!is_string($id) || strlen($id) < 1)
                throw new \Exception("Column ID should be a non-empty string");
            if (!isset($params['type']))
                throw new \Exception("No 'type' param for ID $id");
            if (!in_array($params['type'], self::getAvailableTypes()))
                throw new \Exception("Unknown type '" . $params['type'] . "' for ID $id");
            if (!isset($params['filters']))
                throw new \Exception("No 'filters' param for ID $id");
            if (!isset($params['sortable']))
                throw new \Exception("No 'sortable' param for ID $id");
        }

        $this->columns = $columns;
        return $this;
    }

    /**
     * Columns getter
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Mapper setter
     *
     * @param \Closure $mapper
     * @return AbstractDynamicTable
     */
    public function setMapper(\Closure $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Mapper getter
     *
     * @return \Closure
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Filters setter
     *
     * @param mixed $filters
     * @return AbstractDynamicTable
     */
    public function setFilters($filters)
    {
        if ($filters === null)
            $filters = [];
        else if (!is_array($filters))
            throw new \Exception('Filters should be null or array');

        foreach ($filters as $column => $filter) {
            $found = false;
            foreach ($this->getColumns() as $id => $params) {
                if ($id == $column) {
                    foreach ($filter as $name => $value) {
                        if (!in_array($name, $params['filters']))
                            unset($filter[$name]);
                    }
                    break;
                }
            }

            if (!$found)
                unset($filters[$column]);
        }

        $this->filters = $filters;
        return $this;
    }

    /**
     * JSON version of filters setter
     *
     * @param string $filters
     * @return AbstractDynamicTable
     */
    public function setFiltersJson($filters)
    {
        if ($filters === null)
            return $this->setFilters([]);

        $data = json_decode($filters, true);
        if ($data === null)
            throw new \Exception('Could not decode JSON data');

        return $this->setFilters($data);
    }

    /**
     * Filters getter
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Sort column setter
     *
     * @param string $column
     * @return AbstractDynamicTable
     */
    public function setSortColumn($column)
    {
        $this->sortColumn = null;

        $found = false;
        foreach ($table->getColumns() as $id => $params) {
            if ($id == $column) {
                $found = ($params['sortable'] === true);
                break;
            }
        }

        if ($found)
            $this->sortColumn = $column;

        return $this;
    }

    /**
     * Sort column getter
     *
     * @return string
     */
    public function getSortColumn()
    {
        return $this->sortColumn;
    }

    /**
     * Sort direction setter
     *
     * @param string $dir
     * @return AbstractDynamicTable
     */
    public function setSortDir($dir)
    {
        if (in_array($dir, [ self::DIR_ASC, self::DIR_DESC ]))
            $this->sortDir = $dir;
        else
            $this->sortDir = self::DIR_ASC;

        return $this;
    }

    /**
     * Sort direction getter
     *
     * @return string
     */
    public function getSortDir()
    {
        return $this->sortDir;
    }

    /**
     * Page number setter
     *
     * @param integer $number
     * @return AbstractDynamicTable
     */
    public function setPageNumber($number)
    {
        $this->pageNumber = ($number === null ? 1 : (int)$number);
        if ($this->pageNumber < 1)
            $this->pageNumber = 1;

        return $this;
    }

    /**
     * Page number getter
     *
     * @return integer
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Page size setter
     *
     * @param integer $size
     * @return AbstractDynamicTable
     */
    public function setPageSize($size)
    {
        $this->pageSize = ($size === null ? self::PAGE_SIZE : (int)$size);
        if ($this->pageSize < 0)
            $this->pageSize = self::PAGE_SIZE;

        return $this;
    }

    /**
     * Page size getter
     *
     * @return integer
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Total pages number getter
     *
     * @return integer
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Return table descriptions
     *
     * @return array
     */
    public function describe()
    {
        $columns = [];
        foreach ($this->columns as $id => $params) {
            $columns[$id] = [
                'type'      => $params['type'],
                'filters'   => $params['filters'],
                'sortable'  => $params['sortable'],
            ];
        }

        return [
            'columns' => $columns,
        ];
    }

    /**
     * Fetch data and feed it to front-end
     *
     * @return array
     */
    abstract public function fetch();

    /**
     * List column types
     *
     * @return array
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_STRING,
            self::TYPE_INTEGER,
            self::TYPE_FLOAT,
            self::TYPE_BOOLEAN,
            self::TYPE_DATETIME,
        ];
    }

    /**
     * List filter types
     *
     * @return array
     */
    public static function getAvailableFilters()
    {
        return [
            self::FILTER_LIKE,
            self::FILTER_EQUAL,
            self::FILTER_GREATER,
            self::FILTER_LESS,
            self::FILTER_NULL,
        ];
    }
}
