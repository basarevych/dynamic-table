<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Application\Document\Sample as SampleDocument;

/**
 * Sample document repository
 * 
 * @category    Application
 * @package     Document
 */
class SampleRepository extends DocumentRepository
{
    /**
     * Remove all the documents
     */
    public function removeAll()
    {
        $dm = $this->getDocumentManager();

        $dm->createQueryBuilder('Application\Document\Sample')
            ->remove()
            ->getQuery()
            ->execute();
    }
}
