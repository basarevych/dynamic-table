<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable\Adapter;

use MongoDate;
use MongoRegex;
use Zend\Paginator\Paginator;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use DoctrineMongoODMModule\Paginator\Adapter\DoctrinePaginator;
use DynamicTable\Table;
use DynamicTable\Adapter\AbstractAdapter;

/**
 * Doctrine data adapter class
 *
 * @category    DynamicTable
 * @package     Adapter
 */
class DoctrineMongoOdmAdapter extends AbstractAdapter
{
    /**
     * Doctrine QueryBuilder
     *
     * @var QueryBuilder
     */
    protected $qb = null;

    /**
     * Prepared filtering ODM operators
     *
     * @var array
     */
    protected $andOps = [];

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
            if (!isset($params['field_name']))
                throw new \Exception("No 'field_name' param for ID $id");
        }

        $columns = $table->getColumns();
        $successfulFilters = [];
        foreach ($table->getFilters() as $column => $filters) {
            $successfulNames = [];
            foreach ($filters as $name => $value) {
                if ($this->buildFilter($columns[$column]['field_name'], $columns[$column]['type'], $name, $value))
                    $successfulNames[$name] = $value;
            }
            if (count($successfulNames) > 0)
                $successfulFilters[$column] = $successfulNames;
        }
        $table->setFilters($successfulFilters);
    }

    /**
     * Filter data
     *
     * @param Table $table
     */
    public function filter(Table $table)
    {
        $this->andOps = [];

        $columns = $table->getColumns();
        foreach ($table->getFilters() as $column => $filters) {
            foreach ($filters as $name => $value) {
                $this->buildFilter($columns[$column]['field_name'], $columns[$column]['type'], $name, $value);
            }
        }

        if (count($this->andOps) == 0)
            return;

        $qb = $this->getQueryBuilder();
        foreach ($this->andOps as $field => $orOps) {
            $expr = $qb->expr();
            foreach ($orOps as $op) {
                $operator = $op['operator'];
                if ($operator == 'range') {
                    $value1 = $op['value1'];
                    $value2 = $op['value2'];
                    if (strlen($value1) && strlen($value2)) {
                        $expr->addOr(
                            $qb->expr()->field($field)->range(
                                $value1 instanceof MongoDate ? $value1 : (int)$value1,
                                $value2 instanceof MongoDate ? $value2 : (int)$value2
                            )
                        );
                        $expr->addOr(
                            $qb->expr()->field($field)->equals(
                                $value2 instanceof MongoDate ? $value2 : (int)$value2
                            )
                        );
                    } else if (strlen($value1)) {
                        $expr->addOr(
                            $qb->expr()->field($field)->gte(
                                $value1 instanceof MongoDate ? $value1 : (int)$value1
                            )
                        );
                    } else if (strlen($value2)) {
                        $expr->addOr(
                            $qb->expr()->field($field)->lte(
                                $value2 instanceof MongoDate ? $value2 : (int)$value2
                            )
                        );
                    }
                } else {
                    $value = $op['value'];
                    $expr->addOr(
                        $qb->expr()->field($field)->$operator($value)
                    );
                }
            }
            $qb->addAnd($expr);
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

        $field = null;
        foreach ($table->getColumns() as $id => $params) {
            if ($id == $column) {
                $field = $params['field_name'];
                break;
            }
        }

        if (!$field)
            throw new \Exception("No 'field_name' for column: $column");

        $qb = $this->getQueryBuilder();
        $qb->sort($field, $dir);
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
        $cursor = $query->execute();
        $adapter = new DoctrinePaginator($cursor);
        $paginator = new Paginator($adapter);
        $table->calculatePageParams(count($paginator));

        if ($table->getPageSize() > 0) {
            $paginator->setCurrentPageNumber($table->getPageNumber())
                      ->setItemCountPerPage($table->getPageSize());
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

        if ($type == Table::TYPE_DATETIME) {
            if ($filter == Table::FILTER_BETWEEN
                    && is_array($value) && count($value) == 2) {
                $value = [
                    $value[0] ? new MongoDate($value[0]) : null,
                    $value[1] ? new MongoDate($value[1]) : null,
                ];
            } else if ($filter != Table::FILTER_BETWEEN
                    && is_scalar($value)) {
                $value = new MongoDate($value);
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
                if (!(isset($this->andOps[$field])))
                    $this->andOps[$field] = [];
                $this->andOps[$field][] = [
                    'operator'  => 'equals',
                    'value'     => new MongoRegex('/.*' . preg_quote($value) . '.*/i'),
                ];
                break;
            case Table::FILTER_EQUAL:
                if (!(isset($this->andOps[$field])))
                    $this->andOps[$field] = [];
                $this->andOps[$field][] = [
                    'operator'  => 'equals',
                    'value'     => $value,
                ];
                break;
            case Table::FILTER_BETWEEN:
                if (!(isset($this->andOps[$field])))
                    $this->andOps[$field] = [];
                $this->andOps[$field][] = [
                    'operator'  => 'range',
                    'value1'    => $value[0],
                    'value2'    => $value[1],
                ];
                break;
            case Table::FILTER_NULL:
                if (!(isset($this->andOps[$field])))
                    $this->andOps[$field] = [];
                $this->andOps[$field][] = [
                    'operator'  => 'exists',
                    'value'     => false,
                ];
                break;
            default:
                throw new \Exception("Unknown filter: $filter");
        }

        return true;
    }
}
