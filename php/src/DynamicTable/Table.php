<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable;

use DynamicTable\Adapter\AbstractAdapter;

/**
 * The DynamicTable class
 *
 * @category    DynamicTable
 * @package     DynamicTable
 */
class Table
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
     * @const FILTER_BETWEEN
     * @const FILTER_NULL
     */
    const FILTER_LIKE = 'like';
    const FILTER_EQUAL = 'equal';
    const FILTER_GREATER = 'greater';
    const FILTER_LESS = 'less';
    const FILTER_BETWEEN = 'between';
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
     * Data adapter
     *
     * @var AbstractAdapter
     */
    protected $adapter;

    /**
     * Table columns
     *
     * $columns = [
     *     $columnId => $columnParams,
     *     // ...
     * ];
     * $columnParams = [
     *     'title' => $columnTitle,
     *     'type' => $columnType, // One of TYPE_* constants
     *     'filters' => $columnFilters, // Array of FILTER_* constants
     *     'sortable' => true|false,
     *     'visible' => true|false,
     * ];
     *
     * @var array
     */
    protected $columns = [];

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
     * Adapter setter
     *
     * @param AbstractAdapter $adapter
     * @return Table
     */
    public function setAdapter(AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Adapter getter
     *
     * @return AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Columns setter
     *
     * @param array $columns
     * @throws Exception            In case of invalid column definition
     * @return Table
     */
    public function setColumns(array $columns)
    {
        foreach ($columns as $id => $params) {
            if (!is_string($id) || strlen($id) < 1)
                throw new \Exception("Column ID should be a non-empty string");
            if (!isset($params['title']))
                throw new \Exception("No 'title' param for ID $id");
            if (!isset($params['type']))
                throw new \Exception("No 'type' param for ID $id");
            if (!in_array($params['type'], self::getAvailableTypes()))
                throw new \Exception("Unknown type '" . $params['type'] . "' for ID $id");
            if (!isset($params['filters']))
                throw new \Exception("No 'filters' param for ID $id");
            if (!isset($params['sortable']))
                throw new \Exception("No 'sortable' param for ID $id");
            if (!isset($params['visible']))
                throw new \Exception("No 'visible' param for ID $id");

            foreach ($params['filters'] as $filter) {
                if (!in_array($filter, self::getAvailableFilters()))
                    throw new \Exception("Unknown filter: $filter");
            }
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
     * Filters setter
     *
     * @param mixed $filters
     * @return Table
     */
    public function setFilters($filters)
    {
        if ($filters === null)
            $filters = [];
        else if (!is_array($filters))
            throw new \Exception('Filters should be null or array');

        foreach ($filters as $column => $filter) {
            $found = [];
            foreach ($this->getColumns() as $id => $params) {
                if ($id == $column) {
                    foreach ($filter as $name => $value) {
                        if (in_array($name, $params['filters']))
                            $found[$name] = $value;
                    }
                    break;
                }
            }

            if (count($found) > 0)
                $filters[$column] = $found;
            else
                unset($filters[$column]);
        }

        $this->filters = $filters;
        return $this;
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
     * @return Table
     */
    public function setSortColumn($column)
    {
        $this->sortColumn = null;

        $found = false;
        foreach ($this->getColumns() as $id => $params) {
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
     * @return Table
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
     * @return Table
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
     * @return Table
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
     * Recalculates $pageNumber and $totalPages
     *
     * @param integer $rowCount
     * @return Table
     */
    public function calculatePageParams($rowCount)
    {
        $this->totalPages = $this->pageSize > 0
            ? ceil($rowCount / $this->pageSize)
            : 1;
        if ($this->totalPages <= 0)
            $this->totalPages = 1;
        if ($this->pageNumber > $this->totalPages)
            $this->pageNumber = $this->totalPages;
    }

    /**
     * Return table description
     *
     * @return array
     */
    public function describe()
    {
        $columns = [];
        foreach ($this->columns as $id => $params) {
            $columns[$id] = [
                'title'     => $params['title'],
                'type'      => $params['type'],
                'filters'   => $params['filters'],
                'sortable'  => $params['sortable'],
                'visible'   => $params['visible'],
            ];
        }

        return [
            'columns' => $columns,
        ];
    }

    /**
     * Fetch data and feed id to front-end
     */
    public function fetch()
    {
        $adapter = $this->getAdapter();
        if (!$adapter)
            throw new \Exception("Adapter property is not set");

        $adapter->check($this);
        $adapter->sort($this);
        $adapter->filter($this);
        $result = $adapter->paginate($this);

        $filters = $this->getFilters();
        return [
            'sort_column'   => $this->getSortColumn(),
            'sort_dir'      => $this->getSortDir(),
            'page_number'   => $this->getPageNumber(),
            'page_size'     => $this->getPageSize(),
            'total_pages'   => $this->getTotalPages(),
            'filters'       => count($filters) ? $filters : new \StdClass(),
            'rows'          => $result,
        ];
    }

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
            self::FILTER_BETWEEN,
            self::FILTER_NULL,
        ];
    }
}
