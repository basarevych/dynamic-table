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
    protected $data = [];

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

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
    }

    /**
     * Get data
     *
     * @param Table $table
     * @return array
     */
    public function fetch(Table $table)
    {
        $mapper = $this->getMapper();
        if (!$mapper)
            return $this->data;

        $result = [];
        foreach ($this->data as $row)
            $result[] = $mapper($row);

        return $result;
    }
}
