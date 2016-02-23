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

/**
 * Base data adapter class
 *
 * @category    DynamicTable
 * @package     Adapter
 */
abstract class AbstractAdapter
{
    /**
     * Data source timezone
     *
     * @var string
     */
    protected $dbTimezone = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dbTimezone = date_default_timezone_get();
    }

    /**
     * DB timezone setter
     *
     * @param string $timezone
     * @return Table
     */
    public function setDbTimezone($timezone)
    {
        $this->dbTimezone = $timezone;
    }

    /**
     * DB timezone getter
     *
     * @return string
     */
    public function getDbTimezone()
    {
        return $this->dbTimezone;
    }

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
