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
                $test = $this->checkFilter($name, $columns[$id]['type'], $value, null);
                if ($test !== null)
                    $successfulNames[$name] = $value;
            }
            if (count($successfulNames) > 0)
                $successfulFilters[$id] = $successfulNames;
        }
        $table->setFilters($successfulFilters);
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
            $passedAnds = true;
            foreach ($filters as $id => $filterData) {
                $passedOrs = false;
                foreach ($filterData as $name => $value) {
                    $real = $row[$id];
                    if ($real && $columns[$id]['type'] == Table::TYPE_DATETIME) {
                        if (is_string($real)) {
                            if ($this->getDbTimezone())
                                $real = new \DateTime($real, new \DateTimeZone($this->getDbTimezone()));
                            else
                                $real = new \DateTime($real);
                        } else if (is_int($real)) {
                            $real = new \DateTime('@' . $real);
                        }
                        if (date_default_timezone_get())
                            $real->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                    }
                    $test = $this->checkFilter($name, $columns[$id]['type'], $value, $real);
                    if ($test === true)
                        $passedOrs = true;
                }
                if (!$passedOrs)
                    $passedAnds = false;
            }
            if ($passedAnds)
                $result[] = $row;
        }

        $this->data = $result;
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

        $mapper = $table->getMapper();
        $result = [];
        foreach ($data as $row) {
            foreach ($table->getColumns() as $columnId => $columnParams) {
                $value = $row[$columnId];
                if ($value === null)
                    continue;

                if ($columnParams['type'] == Table::TYPE_DATETIME) {
                    if (is_string($value)) {
                        if ($this->getDbTimezone())
                            $dt = new \DateTime($value, new \DateTimeZone($this->getDbTimezone()));
                        else
                            $dt = new \DateTime($value);
                    } else if (is_int($value)) {
                        $dt = new \DateTime('@' . $value);
                    } else {
                        $dt = $value;
                    }
                    if (date_default_timezone_get())
                        $dt->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                    $row[$columnId] = $dt;
                }
            }

            $result[] = $mapper ? $mapper($row) : $row;
        }

        return $result;
    }

    /**
     * Check and apply filter
     *
     * @param string $filter
     * @param string $type
     * @param mixed $test
     * @param mixed $real
     */
    protected function checkFilter($filter, $type, $test, $real)
    {
        if ($type == Table::TYPE_DATETIME) {
            if ($filter == Table::FILTER_BETWEEN
                    && is_array($test) && count($test) == 2) {
                $test = [
                    $test[0] ? new \DateTime('@' . $test[0]) : null,
                    $test[1] ? new \DateTime('@' . $test[1]) : null,
                ];
                if ($test[0] && $this->getDbTimezone())
                    $test[0]->setTimezone(new \DateTimeZone($this->getDbTimezone()));
                if ($test[1] && $this->getDbTimezone())
                    $test[1]->setTimezone(new \DateTimeZone($this->getDbTimezone()));
            } else if ($filter != Table::FILTER_BETWEEN
                    && is_scalar($test)) {
                $test = new \DateTime('@' . $test);
                if ($this->getDbTimezone())
                    $test->setTimezone(new \DateTimeZone($this->getDbTimezone()));
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
