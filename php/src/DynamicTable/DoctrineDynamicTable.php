<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DynamicTable\AbstractDynamicTable;

/**
 * DynamicTable for Doctrine
 *
 * @category    DynamicTable
 * @package     Doctrine
 */
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
        $query = $this->qb->getQuery();
        $paginator = new Paginator($query);

        $this->totalPages = $this->pageSize > 0
            ? ceil(count($paginator) / $this->pageSize)
            : 1;
        if ($this->totalPages <= 0)
            $this->totalPages = 1;
        if ($this->pageNumber > $this->totalPages)
            $this->pageNumber = $this->totalPages;

        if ($this->pageSize > 0) {
            $paginator->getQuery()
                      ->setFirstResult($this->pageSize * ($this->pageNumber - 1))
                      ->setMaxResults($this->pageSize);
        }

        return $this->getData($paginator);
    }
}
