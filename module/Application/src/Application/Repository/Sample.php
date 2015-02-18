<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Application\Entity\Sample as SampleEntity;

/**
 * Repository for Sample entity
 * 
 * @category    Application
 * @package     Repository
 */
class Sample extends EntityRepository
{
    /**
     * Remove all the table content
     */
    public function removeAll()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'DELETE Application\Entity\Sample s'
        );
        $query->getResult();
    }
} 
