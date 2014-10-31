<?php

namespace DynamicTable;

use Doctrine\ORM\QueryBuilder;
use DynamicTable\AbstractDynamicTable;

class DoctrineDynamicTable extends AbstractDynamicTable
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
     * @return DoctrineDynamicTable
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
     * Columns setter
     *
     * @param array $columns
     * @throws Exception            In case of invalid column definition
     * @return DoctrineDynamicTable
     */
    public function setColumns(array $columns)
    {
        foreach ($columns as $id => $params) {
            if (!isset($params['sql_id']))
                throw new \Exception("No 'sql_id' param for ID $id");
        }

        return parent::setColumns($columns);
    }

    /**
     * Fetch data and feed it to front-end
     *
     * @return array
     */
    public function fetch()
    {
    }
}
