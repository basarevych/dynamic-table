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
use DynamicTable\Adapter\ArrayAdapter;

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

        $table = $this->createTable();
        $table->setAdapter($adapter);

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'describe':
            $data = $table->describe();
            break;
        case 'data':
            $table->setFiltersJson($this->params()->fromQuery('filters'));
            $table->setSortColumn($this->params()->fromQuery('sort_column'));
            $table->setSortDir($this->params()->fromQuery('sort_dir'));
            $table->setPageNumber($this->params()->fromQuery('page_number'));
            $table->setPageSize($this->params()->fromQuery('page_size'));
            $data = $table->fetch();
            break;
        default:
            throw new \Exception('Unknown query type: ' . $query);
        }

        $data['success'] = true;
        return new JsonModel($data);
    }

    /**
     * Table data retrieving action (Array version)
     */
    public function arrayTableAction()
    {
        $data = [];
        $dt = new \DateTime("2010-05-11 13:00:00");
        for ($i = 1; $i <= 100; $i++) {
            $dt->add(new \DateInterval('PT10S'));

            if ($i == 3) {
                $data[] = [
                    'id' => $i,
                    'string' => null,
                    'integer' => null,
                    'float' => null,
                    'boolean' => null,
                    'datetime' => null,
                ];
            } else {
                $data[] = [
                    'id' => $i,
                    'string' => "string $i",
                    'integer' => $i,
                    'float' => $i / 100,
                    'boolean' => ($i % 2 == 0),
                    'datetime' => clone $dt,
                ];
            }
        }

        $adapter = new ArrayAdapter();
        $adapter->setData($data);
        $adapter->setMapper(function ($row) {
            $result = $row;

            if ($row['datetime'] !== null)
                $result['datetime'] = $row['datetime']->getTimestamp();

            return $result;
        });

        $table = $this->createTable();
        $table->setAdapter($adapter);

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'describe':
            $data = $table->describe();
            break;
        case 'data':
            $table->setFiltersJson($this->params()->fromQuery('filters'));
            $table->setSortColumn($this->params()->fromQuery('sort_column'));
            $table->setSortDir($this->params()->fromQuery('sort_dir'));
            $table->setPageNumber($this->params()->fromQuery('page_number'));
            $table->setPageSize($this->params()->fromQuery('page_size'));
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
                'filters'   => [ Table::FILTER_GREATER, Table::FILTER_LESS, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'float' => [
                'title'     => $translate('Float'),
                'sql_id'    => 's.value_float',
                'type'      => Table::TYPE_FLOAT,
                'filters'   => [ Table::FILTER_GREATER, Table::FILTER_LESS, Table::FILTER_NULL ],
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
                'filters'   => [ Table::FILTER_GREATER, Table::FILTER_LESS, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'computed' => [
                'title'     => $translate('Computed Value'),
                'sql_id'    => 'computed',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_GREATER, Table::FILTER_LESS, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
        ]);

        return $table;
    }
}
