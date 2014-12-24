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
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new \Application\Exception\NotFoundException('Action is not found');
    }
}
