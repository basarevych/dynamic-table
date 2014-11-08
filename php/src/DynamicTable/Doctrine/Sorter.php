<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable\Doctrine;

use DynamicTable\Doctrine\DynamicTable;

/**
 * DynamicTable sorting class for Doctrine
 *
 * @category    DynamicTable
 * @package     Doctrine
 */
class Sorter
{
    /**
     * Sort the table
     *
     * @param DynamicTable $table
     */
    public function apply(DynamicTable $table)
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
            throw new \Exception("No 'sql_id' for $column");

        $qb = $table->getQueryBuilder();
        $qb->addOrderBy($sqlId, $dir);
    }
}
