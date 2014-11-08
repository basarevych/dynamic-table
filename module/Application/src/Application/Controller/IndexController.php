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
use DynamicTable\Doctrine\DynamicTable;

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
     * DynamicTable data retrieving action (Database version)
     */
    public function dbDataAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $qb = $em->createQueryBuilder();
        $qb->select('s, s.id + 100 AS computed')
           ->from('Application\Entity\Sample', 's');

        $table = new DynamicTable();
        $table->setQueryBuilder($qb);
        $table->setColumns([
            'id' => [
                'sql_id'        => 's.id',
                'type'          => DynamicTable::TYPE_INTEGER,
                'filterable'    => true,
                'sortable'      => true,
            ],
            'string' => [
                'sql_id'        => 's.value_string',
                'type'          => DynamicTable::TYPE_STRING,
                'filterable'    => true,
                'sortable'      => true,
            ],
            'integer' => [
                'sql_id'        => 's.value_integer',
                'type'          => DynamicTable::TYPE_INTEGER,
                'filterable'    => true,
                'sortable'      => true,
            ],
            'float' => [
                'sql_id'        => 's.value_float',
                'type'          => DynamicTable::TYPE_FLOAT,
                'filterable'    => true,
                'sortable'      => true,
            ],
            'boolean' => [
                'sql_id'        => 's.value_boolean',
                'type'          => DynamicTable::TYPE_BOOLEAN,
                'filterable'    => true,
                'sortable'      => true,
            ],
            'datetime' => [
                'sql_id'        => 's.value_datetime',
                'type'          => DynamicTable::TYPE_DATETIME,
                'filterable'    => true,
                'sortable'      => true,
            ],
            'computed' => [
                'sql_id'        => 'computed',
                'type'          => DynamicTable::TYPE_INTEGER,
                'filterable'    => true,
                'sortable'      => true,
            ],
        ]);
        $table->setMapper(function ($row) use ($translate) {
            $boolean = $row[0]->getValueBoolean();
            if ($boolean !== null)
                $boolean = $translate($boolean ? 'TRUE_VALUE' : 'FALSE_VALUE');
            $datetime = $row[0]->getValueDatetime();
            if ($datetime !== null)
                $datetime = $datetime->format('Y-m-d H:i:s');

            return [
                'id'        => $row[0]->getId(),
                'string'    => $row[0]->getValueString(),
                'integer'   => $row[0]->getValueInteger(),
                'float'     => $row[0]->getValueFloat(),
                'boolean'   => $boolean,
                'datetime'  => $datetime,
                'computed'  => $row['computed'],
            ];
        });

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'discover':
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
}
