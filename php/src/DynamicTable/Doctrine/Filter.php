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
 * DynamicTable filtering class for Doctrine
 *
 * @category    DynamicTable
 * @package     Doctrine
 */
class Filter
{
    protected $sqlOrs = [];
    protected $sqlParams = [];

    /**
     * Filter the table
     *
     * @param DynamicTable $table
     */
    public function apply(DynamicTable $table)
    {
        $this->sqlOrs = [];
        $this->sqlParams = [];

        $columns = $table->getColumns();
        $filters = $table->getFilters();

        foreach ($filters as $column => $filter) {
            foreach ($filter as $name => $value)
                $this->buildFilter($columns[$column]['sql_id'], $columns[$column]['type'], $name, $value);
        }

        if (count($this->sqlOrs) == 0)
            return;

        $qb = $table->getQueryBuilder();
        $qb->andWhere(join(' OR ', $this->sqlOrs));
        foreach ($this->sqlParams as $name => $value)
            $qb->setParameter($name, $value);
    }

    /**
     * Set SQL query params for a filter
     *
     * @param string $field
     * @param string $type
     * @param string $filter
     * @param string $value
     */
    protected function buildFilter($field, $type, $filter, $value)
    {
        $paramBaseName = str_replace('.', '_', $field);

        if ($type == DynamicTable::TYPE_DATETIME)
            $value = new \DateTime('@' . $value);

        if ((is_array($value) && count($value) == 2) || strlen($value) > 0) {
            switch ($filter) {
                case DynamicTable::FILTER_LIKE:
                    $param = $paramBaseName . '_like';
                    $this->sqlOrs[] = "($field LIKE :$param)";
                    $this->sqlParams[$param] = '%' . $value . '%';
                    break;
                case DynamicTable::FILTER_EQUAL:
                    $param = $paramBaseName . '_equal';
                    $this->sqlOrs[] = "($field = :$param)";
                    $this->sqlParams[$param] = $value;
                    break;
                case DynamicTable::FILTER_GREATER:
                    $param = $paramBaseName . '_greater';
                    $this->sqlOrs[] = "($field > :$param)";
                    $this->sqlParams[$param] = $value;
                    break;
                case DynamicTable::FILTER_LESS:
                    $param = $paramBaseName . '_less';
                    $this->sqlOrs[] = "($field < :$param)";
                    $this->sqlParams[$param] = $value;
                    break;
                case DynamicTable::FILTER_BETWEEN:
                    $param1 = $paramBaseName . '_begin';
                    $param2 = $paramBaseName . '_end';
                    $this->sqlOrs[] = "($field > :$param1 AND $field < :$param2)";
                    $this->sqlParams[$param1] = $value[0];
                    $this->sqlParams[$param2] = $value[1];
                    break;
                case DynamicTable::FILTER_NULL:
                    $param = $value ? 'NULL' : 'NOT NULL';
                    $this->sqlOrs[] = "($field IS $param)";
                    break;
            }
        }
    }
}
