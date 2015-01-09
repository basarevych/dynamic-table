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
     *
     * @return JsonModel
     */
    public function doctrineTableAction()
    {
        $table = $this->createTable();

        $this->connectDoctrineData($table);

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'describe':
            $data = $table->describe();
            break;
        case 'data':
            $data = $table->setPageParams($_GET)->fetch();
            break;
        default:
            throw new \Exception('Unknown query type: ' . $query);
        }

        $data['success'] = true;
        return new JsonModel($data);
    }

    /**
     * Table data retrieving action (Array version)
     *
     * @return JsonModel
     */
    public function arrayTableAction()
    {
        $table = $this->createTable();

        $this->connectArrayData($table);

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'describe':
            $data = $table->describe();
            break;
        case 'data':
            $data = $table->setPageParams($_GET)->fetch();
            break;
        default:
            throw new \Exception('Unknown query type: ' . $query);
        }

        $data['success'] = true;
        return new JsonModel($data);
    }

    /**
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new \Application\Exception\NotFoundException('Action is not found');
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
        ]);

        return $table;
    }

    /**
     * Create Doctrine adapter and mapper
     *
     * @param Table $table
     */
    protected function connectDoctrineData($table)
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $qb = $em->createQueryBuilder();
        $qb->select('s')
           ->from('Application\Entity\Sample', 's');

        $adapter = new DoctrineAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($escapeHtml) {
            $datetime = $row->getValueDatetime();
            if ($datetime !== null)
                $datetime = $datetime->getTimestamp();

            return [
                'id'        => $row->getId(),
                'string'    => $escapeHtml($row->getValueString()),
                'integer'   => $row->getValueInteger(),
                'float'     => $row->getValueFloat(),
                'boolean'   => $row->getValueBoolean(),
                'datetime'  => $datetime,
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }

    /**
     * Create Array adapter and mapper
     *
     * @param Table $table
     */
    protected function connectArrayData($table)
    {
        $sl = $this->getServiceLocator();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

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

        $mapper = function ($row) use ($escapeHtml) {
            $result = $row;

            $result['string'] = $escapeHtml($row['string']);

            if ($row['datetime'] !== null)
                $result['datetime'] = $row['datetime']->getTimestamp();

            return $result;
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
