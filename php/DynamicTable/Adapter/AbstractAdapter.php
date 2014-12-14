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
     * Check table and data
     *
     * @param Table $table
     * @throws \Exception       Throw when error found
     */
    abstract public function check(Table $table);

    /**
     * Filter data
     *
     * @param Table $table
     */
    abstract public function filter(Table $table);

    /**
     * Sort data
     *
     * @param Table $table
     */
    abstract public function sort(Table $table);

    /**
     * Paginate and return result
     *
     * @param Table $table
     * @return array
     */
    abstract public function paginate(Table $table);
}
