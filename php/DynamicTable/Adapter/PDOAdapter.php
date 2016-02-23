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
use DynamicTable\Adapter\GenericDBAdapter;

/**
 * PDO adapter class
 *
 * @category    DynamicTable
 * @package     Adapter
 */
class PDOAdapter extends GenericDBAdapter
{
    /**
     * PDO instance
     *
     * @var mixed
     */
    protected $pdo = null;

    /**
     * PDO instance setter
     *
     * @param mixed $pdo
     * @return PDOAdapter
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * PDO instance getter
     *
     * @return PDOAdapter
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Paginate and return result
     *
     * @param Table $table
     * @return array
     */
    public function paginate(Table $table)
    {
        $ands = [];
        foreach ($this->sqlAnds as $filter => $ors)
            $ands[] = '(' . join(') OR (', $ors) . ')';

        $where = '';
        if (strlen($this->initialWhere) > 0) {
            $where = ' WHERE (' . $this->initialWhere . ')';
            if (count($ands))
                $where .= ' AND (' . join(') AND (', $ands) . ')';
        } else if (count($ands)) {
            $where = ' WHERE (' . join(') AND (', $ands) . ')';
        }

        $preparedParams = [];
        foreach ($this->sqlParams as $name => $value) {
            if ($value instanceof \DateTime)
                $preparedParams[$name] = $value->format('Y-m-d H:i:s');
            else
                $preparedParams[$name] = $value;
        }

        $db = $this->getPdo();
        $mapper = $table->getMapper();
        if (!$mapper)
            throw new \Exception("Data 'mapper' is required when using PDOAdapter");

        $sql = "SELECT COUNT(*) AS count"
             . "  FROM " . $this->initialFrom . " "
             . $where . " ";
        $sth = $db->prepare($sql);
        $sth->execute($preparedParams);
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $count = $result[0]['count'];

        $table->calculatePageParams($count);

        if ($this->sqlOrderBy)
            $where .= ' ORDER BY ' . $this->sqlOrderBy . ' ';
        if ($table->getPageSize() > 0) {
            $where .= ' LIMIT ' . $table->getPageSize() . ' ';
            $where .= ' OFFSET ' . ($table->getPageSize() * ($table->getPageNumber() - 1)) . ' ';
        }

        $sql = "SELECT " . $this->initialSelect . " "
             . "  FROM " . $this->initialFrom . " "
             . $where . " ";
        $sth = $db->prepare($sql);
        $sth->execute($preparedParams);
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        $data = [];
        foreach ($result as $row) {
            foreach ($table->getColumns() as $columnId => $columnParams) {
                $value = $row[$columnId];
                if ($value === null)
                    continue;

                if ($columnParams['type'] == Table::TYPE_DATETIME) {
                    if (is_string($value)) {
                        if ($this->getDbTimezone())
                            $dt = new \DateTime($value, new \DateTimeZone($this->getDbTimezone()));
                        else
                            $dt = new \DateTime($value);
                    } else if (is_int($value)) {
                        $dt = new \DateTime('@' . $value);
                    } else {
                        $dt = $value;
                    }
                    if (date_default_timezone_get())
                        $dt->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                    $row[$columnId] = $dt;
                }
            }

            $data[] = $mapper ? $mapper($row) : $row;
        }

        return $data;
    }

    /**
     * Build SQL query for a filter
     *
     * @param string $field
     * @param string $type
     * @param string $filter
     * @param mixed $value
     * @return boolean True on success
     */
    protected function buildFilter($field, $type, $filter, $value)
    {
        if (strlen($field) == 0)
            throw new \Exception("Empty 'field'");
        if (strlen($type) == 0)
            throw new \Exception("Empty 'type'");

        if ($type == Table::TYPE_DATETIME) {
            if ($filter == Table::FILTER_BETWEEN
                    && is_array($value) && count($value) == 2) {
                $value = [
                    $value[0] ? new \DateTime('@' . $value[0]) : null,
                    $value[1] ? new \DateTime('@' . $value[1]) : null,
                ];
                if ($value[0] && $this->getDbTimezone())
                    $value[0]->setTimezone(new \DateTimeZone($this->getDbTimezone()));
                if ($value[1] && $this->getDbTimezone())
                    $value[1]->setTimezone(new \DateTimeZone($this->getDbTimezone()));
            } else if ($filter != Table::FILTER_BETWEEN
                    && !is_array($value)) {
                $value = new \DateTime('@' . $value);
                if ($this->getDbTimezone())
                    $value->setTimezone(new \DateTimeZone($this->getDbTimezone()));
            } else {
                return false;
            }
        } else {
            if ($filter == Table::FILTER_BETWEEN) {
                if (!is_array($value) || count($value) != 2)
                    return false;
            } else if (is_array($value)) {
                return false;
            }
        }

        if (!isset($this->sqlAnds[$field]))
            $this->sqlAnds[$field] = [];

        $paramBaseName = 'dt_' . str_replace('.', '_', $field);
        switch ($filter) {
            case Table::FILTER_LIKE:
                $param = ':' . $paramBaseName . '_like';
                $this->sqlAnds[$field][] = "$field LIKE $param";
                $this->sqlParams[$param] = '%' . $value . '%';
                break;
            case Table::FILTER_EQUAL:
                $param = ':' . $paramBaseName . '_equal';
                $this->sqlAnds[$field][] = "$field = $param";
                $this->sqlParams[$param] = $value;
                break;
            case Table::FILTER_BETWEEN:
                $ands = [];
                if ($value[0] !== null) {
                    $param = ':' . $paramBaseName . '_begin';
                    $ands[] = "$field >= $param";
                    $this->sqlParams[$param] = $value[0];
                }
                if ($value[1] !== null) {
                    $param = ':' . $paramBaseName . '_end';
                    $ands[] = "$field <= $param";
                    $this->sqlParams[$param] = $value[1];
                }
                $this->sqlAnds[$field][] = join(' AND ', $ands);
                break;
            case Table::FILTER_NULL:
                $this->sqlAnds[$field][] = "$field IS NULL";
                break;
            default:
                throw new \Exception("Unknown filter: $filter");
        }

        return true;
    }
}
