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

/**
 * Base data adapter class
 *
 * @category    DynamicTable
 * @package     Adapter
 */
abstract class AbstractAdapter
{
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
     * Mapper setter
     *
     * @param \Closure $mapper
     * @return AbstractAdapter
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
     * Sort data
     *
     * @param Table $table
     */
    abstract public function sortData(Table $table);

    /**
     * Filter data
     *
     * @param Table $table
     */
    abstract public function filterData(Table $table);

    /**
     * Get data
     *
     * @param Table $table
     * @return array
     */
    abstract public function getData(Table $table);
}
