<?php
/**
 * DynamicTable
 *
 * @link        https://github.com/basarevych/dynamic-table
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DynamicTable\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DynamicTable\AbstractDynamicTable;
use DynamicTable\Doctrine\Sorter;
use DynamicTable\Doctrine\Filter;

/**
 * DynamicTable for Doctrine
 *
 * @category    DynamicTable
 * @package     Doctrine
 */
class DynamicTable extends AbstractDynamicTable
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
     * @return DynamicTable
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
     * This method adds 'sql_id' parameter to the columns
     *
     * @param array $columns
     * @throws Exception            In case of invalid column definition
     * @return DynamicTable
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
        $sorter = new Sorter();
        $sorter->apply($this);

        $filter = new Filter();
        $filter->apply($this);

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

        $mapper = $this->getMapper();
        if (!$mapper)
            throw new \Exception("Data 'mapper' is not set for the table");

        $result = [];
        foreach ($paginator as $row)
            $result[] = $mapper($row);

        $filters = $this->getFilters();
        return [
            'sort_column'   => $this->getSortColumn(),
            'sort_dir'      => $this->getSortDir(),
            'page_number'   => $this->getPageNumber(),
            'page_size'     => $this->getPageSize(),
            'total_pages'   => $this->getTotalPages(),
            'filters'       => count($filters) ? $filters : new \StdClass(),
            'data'          => $result,
        ];
    }
}
