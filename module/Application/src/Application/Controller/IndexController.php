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
        $sl = $this->getServiceLocator();
        $cache = $sl->has('Memcached') ? $sl->get('Memcached') : null;

        if ($cache && $cache->hasItem('test')) {
            $test = $cache->getItem('test');
        } else {
            $test = 'value';
            if ($cache)
                $cache->setItem('test', $test);
        }

        var_dump($test);

        return new ViewModel();
    }
}
