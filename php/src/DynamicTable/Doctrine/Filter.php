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
        $successfulFilters = [];
        foreach ($table->getFilters() as $column => $filters) {
            $successfulNames = [];
            foreach ($filters as $name => $value) {
                if ($this->buildFilter($columns[$column]['sql_id'], $columns[$column]['type'], $name, $value))
                    $successfulNames[$name] = $value;
            }
            if (count($successfulNames) > 0)
                $successfulFilters[$column] = $successfulNames;
        }
        $table->setFilters($successfulFilters);

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
     * @return boolean          True on success
     */
    protected function buildFilter($field, $type, $filter, $value)
    {
        if ((is_array($value) && count($value) != 2)
                || (!is_array($value) && strlen($value) == 0)) {
            return false;
        }
        if (strlen($field) == 0)
            throw new \Exception("Empty 'field'");
        if (strlen($type) == 0)
            throw new \Exception("Empty 'type'");

        $paramBaseName = str_replace('.', '_', $field);

        if ($type == DynamicTable::TYPE_DATETIME) {
            if (is_array($value)) {
                $value = [
                    new \DateTime('@' . $value[0]),
                    new \DateTime('@' . $value[1]),
                ];
            } else {
                $value = new \DateTime('@' . $value);
            }
        }

        switch ($filter) {
            case DynamicTable::FILTER_LIKE:
                if (is_array($value))
                    return false;
                $param = $paramBaseName . '_like';
                $this->sqlOrs[] = "($field LIKE :$param)";
                $this->sqlParams[$param] = '%' . $value . '%';
                break;
            case DynamicTable::FILTER_EQUAL:
                if (is_array($value))
                    return false;
                $param = $paramBaseName . '_equal';
                $this->sqlOrs[] = "($field = :$param)";
                $this->sqlParams[$param] = $value;
                break;
            case DynamicTable::FILTER_GREATER:
                if (is_array($value))
                    return false;
                $param = $paramBaseName . '_greater';
                $this->sqlOrs[] = "($field > :$param)";
                $this->sqlParams[$param] = $value;
                break;
            case DynamicTable::FILTER_LESS:
                if (is_array($value))
                    return false;
                $param = $paramBaseName . '_less';
                $this->sqlOrs[] = "($field < :$param)";
                $this->sqlParams[$param] = $value;
                break;
            case DynamicTable::FILTER_BETWEEN:
                if (!is_array($value))
                    return false;
                $param1 = $paramBaseName . '_begin';
                $param2 = $paramBaseName . '_end';
                $this->sqlOrs[] = "($field > :$param1 AND $field < :$param2)";
                $this->sqlParams[$param1] = $value[0];
                $this->sqlParams[$param2] = $value[1];
                break;
            case DynamicTable::FILTER_NULL:
                if (is_array($value))
                    return false;
                $param = $value ? 'NULL' : 'NOT NULL';
                $this->sqlOrs[] = "($field IS $param)";
                break;
            default:
                throw new \Exception("Unknown filter: $filter");
        }

        return true;
    }
}
