<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable\Adapter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DynamicTable\Table;
use DynamicTable\Adapter\AbstractAdapter;

/**
 * Doctrine data adapter class
 *
 * @category    DynamicTable
 * @package     Adapter
 */
class DoctrineAdapter extends AbstractAdapter
{
    /**
     * Doctrine QueryBuilder
     *
     * @var QueryBuilder
     */
    protected $qb = null;

    /**
     * QueryBuilder setter
     *
     * @param QueryBuilder $qb
     * @return Table
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->qb = $qb;
        return $this;
    }

    /**
     * QueryBuilder getter
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * Check table and data
     *
     * @param Table $table
     * @throws \Exception       Throw when error found
     */
    public function check(Table $table)
    {
        foreach ($table->getColumns() as $id => $params) {
            if (!isset($params['sql_id']))
                throw new \Exception("No 'sql_id' param for ID $id");
        }
    }

    /**
     * Sort data
     *
     * @param Table $table
     */
    public function sort(Table $table)
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
            throw new \Exception("No 'sql_id' for column: $column");

        $qb = $this->getQueryBuilder();
        $qb->addOrderBy($sqlId, $dir);
    }

    /**
     * Filter data
     *
     * @param Table $table
     */
    public function filter(Table $table)
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

        $qb = $this->getQueryBuilder();
        $qb->andWhere(join(' OR ', $this->sqlOrs));
        foreach ($this->sqlParams as $name => $value)
            $qb->setParameter($name, $value);
    }

    /**
     * Get data
     *
     * @param Table $table
     * @return array
     */
    public function fetch(Table $table)
    {
        $query = $this->getQueryBuilder()->getQuery();
        $paginator = new Paginator($query);
        $table->calculatePageParams(count($paginator));

        if ($table->getPageSize() > 0) {
            $paginator->getQuery()
                      ->setFirstResult($table->getPageSize() * ($table->getPageNumber() - 1))
                      ->setMaxResults($table->getPageSize());
        }

        $mapper = $this->getMapper();
        if (!$mapper)
            throw new \Exception("Data 'mapper' is not set for the table");

        $result = [];
        foreach ($paginator as $row)
            $result[] = $mapper($row);

        return $result;
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

        if ($type == Table::TYPE_DATETIME) {
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
            case Table::FILTER_LIKE:
                if (is_array($value))
                    return false;
                $param = $paramBaseName . '_like';
                $this->sqlOrs[] = "($field LIKE :$param)";
                $this->sqlParams[$param] = '%' . $value . '%';
                break;
            case Table::FILTER_EQUAL:
                if (is_array($value))
                    return false;
                $param = $paramBaseName . '_equal';
                $this->sqlOrs[] = "($field = :$param)";
                $this->sqlParams[$param] = $value;
                break;
            case Table::FILTER_GREATER:
                if (is_array($value))
                    return false;
                $param = $paramBaseName . '_greater';
                $this->sqlOrs[] = "($field > :$param)";
                $this->sqlParams[$param] = $value;
                break;
            case Table::FILTER_LESS:
                if (is_array($value))
                    return false;
                $param = $paramBaseName . '_less';
                $this->sqlOrs[] = "($field < :$param)";
                $this->sqlParams[$param] = $value;
                break;
            case Table::FILTER_BETWEEN:
                if (!is_array($value))
                    return false;
                $param1 = $paramBaseName . '_begin';
                $param2 = $paramBaseName . '_end';
                $this->sqlOrs[] = "($field > :$param1 AND $field < :$param2)";
                $this->sqlParams[$param1] = $value[0];
                $this->sqlParams[$param2] = $value[1];
                break;
            case Table::FILTER_NULL:
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
