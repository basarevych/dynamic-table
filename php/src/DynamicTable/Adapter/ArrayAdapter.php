<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable\Adapter;

use DynamicTable\Table;
use DynamicTable\Adapter\AbstractAdapter;

/**
 * Array data adapter class
 *
 * @category    DynamicTable
 * @package     Adapter
 */
class ArrayAdapter extends AbstractAdapter
{
    /**
     * The data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Data setter
     *
     * @param array $data
     * @return ArrayAdapter
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Data getter
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check table and data
     *
     * @param Table $table
     * @throws \Exception       Throw when error found
     */
    public function check(Table $table)
    {
        $columns = $table->getColumns();
        $filters = $table->getFilters();
        $successfulFilters = [];
        foreach ($filters as $id => $filterData) {
            $successfulNames = [];
            foreach ($filterData as $name => $value) {
                $test = $this->checkFilter($name, $columns[$id]['type'], $value, 0);
                if ($test !== null)
                    $successfulNames[$name] = $value;
            }
            if (count($successfulNames) > 0)
                $successfulFilters[$id] = $successfulNames;
        }
        $table->setFilters($successfulFilters);
    }

    /**
     * Sort data
     *
     * @param Table $table
     */
    public function sort(Table $table)
    {
        $columns = $table->getColumns();
        $column = $table->getSortColumn();
        $dir = $table->getSortDir();

        if (!$column)
            return;

        $type = $columns[$column]['type'];
        $cmp = function ($a, $b) use ($column, $dir, $type) {
            $a = $a[$column];
            $b = $b[$column];

            if ($a === null && $b !== null)
                return $dir == Table::DIR_ASC ? -1 : 1;
            if ($a !== null && $b === null)
                return $dir == Table::DIR_ASC ? 1 : -1;
            if ($a === null && $b === null)
                return 0;

            switch ($type) {
                case Table::TYPE_BOOLEAN:
                    $a = $a ? 1 : 0;
                    $b = $b ? 1 : 0;
                case Table::TYPE_INTEGER:
                case Table::TYPE_FLOAT:
                case Table::TYPE_DATETIME:
                    if ($a == $b)
                        return 0;
                    if ($dir == Table::DIR_ASC)
                        return ($a < $b) ? -1 : 1;
                    return ($a < $b) ? 1 : -1;
                case Table::TYPE_STRING:
                    if ($dir == Table::DIR_ASC)
                        return strcmp($a, $b);
                    return strcmp($b, $a);
                default:
                    throw new \Exception("Unknown field type: $type");
            }
        };

        if (!uasort($this->data, $cmp))
            throw new \Exception('PHP sort failed');
    }

    /**
     * Filter data
     *
     * @param Table $table
     */
    public function filter(Table $table)
    {
        $filters = $table->getFilters();
        if (count($filters) == 0)
            return;

        $columns = $table->getColumns();
        $result = [];
        foreach ($this->data as $row) {
            $passed = false;
            foreach ($filters as $id => $filterData) {
                foreach ($filterData as $name => $value) {
                    $test = $this->checkFilter($name, $columns[$id]['type'], $value, $row[$id]);
                    if ($test === true)
                        $passed = true;
                }
            }
            if ($passed)
                $result[] = $row;
        }

        $this->data = $result;
    }

    /**
     * Paginate and return result
     *
     * @param Table $table
     * @return array
     */
    public function paginate(Table $table)
    {
        $table->calculatePageParams(count($this->data));

        if ($table->getPageSize() > 0) {
            $offset = $table->getPageSize() * ($table->getPageNumber() - 1);
            $length = $table->getPageSize();
            $data = array_slice($this->data, $offset, $length);
        } else {
            $data = $this->data;
        }

        $mapper = $this->getMapper();
        if (!$mapper)
            return $data;

        $result = [];
        foreach ($data as $row)
            $result[] = $mapper($row);

        return $result;
    }

    protected function checkFilter($filter, $type, $test, $real)
    {
        if ($type == Table::TYPE_DATETIME) {
            if ($filter == Table::FILTER_BETWEEN
                    && is_array($test) && count($test) == 2) {
                $test = [
                    $test[0] ? new \DateTime('@' . $test[0]) : null,
                    $test[1] ? new \DateTime('@' . $test[1]) : null,
                ];
            } else if ($filter != Table::FILTER_BETWEEN
                    && is_scalar($test)) {
                $test = new \DateTime('@' . $test);
            } else {
                return null;
            }
        } else {
            if ($filter == Table::FILTER_BETWEEN) {
                if (!is_array($test) || count($test) != 2)
                    return null;
            } else if (!is_scalar($test)) {
                return null;
            }
        }

        switch ($filter) {
            case Table::FILTER_LIKE:
                return $real !== null && strpos($real, $test) !== false;
            case Table::FILTER_EQUAL:
                return $real !== null && $test == $real;
            case Table::FILTER_BETWEEN:
                if ($real === null)
                    return false;
                if ($test[0] !== null && $real < $test[0])
                    return false;
                if ($test[1] !== null && $real > $test[1])
                    return false;
                return true;
            case Table::FILTER_NULL:
                return $real === null;
            default:
                throw new \Exception("Unknown filter: $filter");
        }

        return false;
    }
}
