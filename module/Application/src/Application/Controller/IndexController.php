<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DynamicTable\Table;
use DynamicTable\Adapter\DoctrineAdapter;

/**
 * Index controller
 *
 * @category    Application
 * @package     Controller
 */
class IndexController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * Table data retrieving action (Database version)
     */
    public function doctrineTableAction()
    {
        $table = $this->createTable();
        $adapter = $this->createAdapter();
        $table->setAdapter($adapter);

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'describe':
            $data = $table->describe();
            break;
        case 'data':
            $filters = $this->params()->fromQuery('filters');
            $table->setFilters(json_decode($filters, true));

            $column = $this->params()->fromQuery('sort_column');
            $table->setSortColumn(json_decode($column, true));

            $dir = $this->params()->fromQuery('sort_dir');
            $table->setSortDir(json_decode($dir, true));

            $page = $this->params()->fromQuery('page_number');
            $table->setPageNumber(json_decode($page, true));

            $size = $this->params()->fromQuery('page_size');
            $table->setPageSize(json_decode($size, true));

            $data = $table->fetch();
            break;
        default:
            throw new \Exception('Unknown query type: ' . $query);
        }

        $data['success'] = true;
        return new JsonModel($data);
    }

    /**
     * Create Table object
     *
     * @return Table
     */
    protected function createTable()
    {
        $sl = $this->getServiceLocator();
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $table = new Table();

        $table->setColumns([
            'id' => [
                'title'     => $translate('ID'),
                'sql_id'    => 's.id',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_EQUAL ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'string' => [
                'title'     => $translate('String'),
                'sql_id'    => 's.value_string',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'integer' => [
                'title'     => $translate('Integer'),
                'sql_id'    => 's.value_integer',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'float' => [
                'title'     => $translate('Float'),
                'sql_id'    => 's.value_float',
                'type'      => Table::TYPE_FLOAT,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'boolean' => [
                'title'     => $translate('Boolean'),
                'sql_id'    => 's.value_boolean',
                'type'      => Table::TYPE_BOOLEAN,
                'filters'   => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'datetime' => [
                'title'     => $translate('DateTime'),
                'sql_id'    => 's.value_datetime',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'computed' => [
                'title'     => $translate('Computed Value'),
                'sql_id'    => 'computed',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [],
                'sortable'  => true,
                'visible'   => true,
            ],
        ]);

        return $table;
    }

    /**
     * Create adapter
     *
     * @return DoctrineAdapter
     */
    protected function createAdapter()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');

        $qb = $em->createQueryBuilder();
        $qb->select('s, s.id + 100 AS computed')
           ->from('Application\Entity\Sample', 's');

        $adapter = new DoctrineAdapter();
        $adapter->setQueryBuilder($qb);
        $adapter->setMapper(function ($row) {
            $datetime = $row[0]->getValueDatetime();
            if ($datetime !== null)
                $datetime = $datetime->getTimestamp();

            return [
                'id'        => $row[0]->getId(),
                'string'    => $row[0]->getValueString(),
                'integer'   => $row[0]->getValueInteger(),
                'float'     => $row[0]->getValueFloat(),
                'boolean'   => $row[0]->getValueBoolean(),
                'datetime'  => $datetime,
                'computed'  => $row['computed'],
            ];
        });

        return $adapter;
    }
}
