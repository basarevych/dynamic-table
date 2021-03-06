<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
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
class DoctrineORMAdapter extends AbstractAdapter
{
    /**
     * Doctrine QueryBuilder
     *
     * @var QueryBuilder
     */
    protected $qb = null;

    /**
     * SQL WHERE as an array of ORs
     *
     * @var array
     */
    protected $sqlWhere = [];

    /**
     * Bound SQL parameters
     *
     * @var array
     */
    protected $sqlParams = [];

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

        $backupSqlWhere = $this->sqlWhere;
        $backupSqlParams = $this->sqlParams;

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

        $this->sqlWhere = $backupSqlWhere;
        $this->sqlParams = $backupSqlParams;
    }

    /**
     * Filter data
     *
     * @param Table $table
     */
    public function filter(Table $table)
    {
        $this->sqlWhere = [];
        $this->sqlParams = [];

        $columns = $table->getColumns();
        foreach ($table->getFilters() as $column => $filters) {
            foreach ($filters as $name => $value) {
                $this->buildFilter($columns[$column]['sql_id'], $columns[$column]['type'], $name, $value);
            }
        }

        if (count($this->sqlWhere) == 0)
            return;

        $ands = [];
        foreach ($this->sqlWhere as $filter => $ors)
            $ands[] = '(' . join(') OR (', $ors) . ')';

        $qb = $this->getQueryBuilder();
        $qb->andWhere('(' . join(') AND (', $ands) .')');
        foreach ($this->sqlParams as $name => $value)
            $qb->setParameter($name, $value);
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
     * Paginate and return result
     *
     * @param Table $table
     * @return array
     */
    public function paginate(Table $table)
    {
        $query = $this->getQueryBuilder()->getQuery();
        $paginator = new Paginator($query);
        $table->calculatePageParams(count($paginator));

        if ($table->getPageSize() > 0) {
            $paginator->getQuery()
                      ->setFirstResult($table->getPageSize() * ($table->getPageNumber() - 1))
                      ->setMaxResults($table->getPageSize());
        }

        $mapper = $table->getMapper();
        if (!$mapper)
            throw new \Exception("Data 'mapper' is required when using DoctrineAdapter");

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
                || (!is_array($value) && ($value !== false && strlen($value) == 0))) {
            return false;
        }
        if (strlen($field) == 0)
            throw new \Exception("Empty 'field'");
        if (strlen($type) == 0)
            throw new \Exception("Empty 'type'");

        $paramBaseName = 'dt_' . str_replace('.', '_', $field);

        if ($type == Table::TYPE_DATETIME) {
            if ($filter == Table::FILTER_BETWEEN
                    && is_array($value) && count($value) == 2) {
                $value = [
                    $value[0] ? new \DateTime('@' . $value[0]) : null,
                    $value[1] ? new \DateTime('@' . $value[1]) : null,
                ];
                if ($value[0])
                    $value[0]->setTimezone(new \DateTimeZone($this->getDbTimezone()));
                if ($value[1])
                    $value[1]->setTimezone(new \DateTimeZone($this->getDbTimezone()));
            } else if ($filter != Table::FILTER_BETWEEN
                    && is_scalar($value)) {
                $value = new \DateTime('@' . $value);
                $value->setTimezone(new \DateTimeZone($this->getDbTimezone()));
            } else {
                return false;
            }
        } else {
            if ($filter == Table::FILTER_BETWEEN) {
                if (!is_array($value) || count($value) != 2)
                    return false;
            } else if (!is_scalar($value)) {
                return false;
            }
        }

        switch ($filter) {
            case Table::FILTER_LIKE:
                $param = $paramBaseName . '_like';
                if (!(isset($this->sqlWhere[$field])))
                    $this->sqlWhere[$field] = [];
                $this->sqlWhere[$field][] = "$field LIKE :$param";
                $this->sqlParams[$param] = '%' . $value . '%';
                break;
            case Table::FILTER_EQUAL:
                $param = $paramBaseName . '_equal';
                if (!(isset($this->sqlWhere[$field])))
                    $this->sqlWhere[$field] = [];
                $this->sqlWhere[$field][] = "$field = :$param";
                $this->sqlParams[$param] = $value;
                break;
            case Table::FILTER_BETWEEN:
                $ands = [];
                if ($value[0] !== null) {
                    $param1 = $paramBaseName . '_begin';
                    $ands[] = "$field >= :$param1";
                    $this->sqlParams[$param1] = $value[0];
                }
                if ($value[1] !== null) {
                    $param2 = $paramBaseName . '_end';
                    $ands[] = "$field <= :$param2";
                    $this->sqlParams[$param2] = $value[1];
                }
                if (!(isset($this->sqlWhere[$field])))
                    $this->sqlWhere[$field] = [];
                $this->sqlWhere[$field][] = join(' AND ', $ands);
                break;
            case Table::FILTER_NULL:
                if (!(isset($this->sqlWhere[$field])))
                    $this->sqlWhere[$field] = [];
                $this->sqlWhere[$field][] = "$field IS NULL";
                break;
            default:
                throw new \Exception("Unknown filter: $filter");
        }

        return true;
    }
}
