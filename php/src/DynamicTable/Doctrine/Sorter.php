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
    public function apply(DynamicTable $table)
    {
        $column = $table->getSortColumn();
        $dir = $table->getSortDir();

        if (!$column)
            return;

        $found = false;
        $sqlId = null;
        foreach ($table->getColumns() as $id => $params) {
            if ($id == $column) {
                $found = ($params['sortable'] === true);
                $sqlId = $params['sql_id'];
                break;
            }
        }

        if (!$found) {
            $table->setSortColumn(null);
            return;
        }

        $qb = $table->getQueryBuilder();
        $qb->addOrderBy($sqlId, $dir);
    }
}
