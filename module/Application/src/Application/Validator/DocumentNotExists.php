<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Validator;

use Exception;
use Zend\Validator\AbstractValidator;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * DocumentNotExists validator
 *
 * Usage:
 *
 * $params = [
 *      'documentManager' => $dm,                           // Doctrine DocumentManager instance
 *      'document'        => 'Application\Document\Sample', // The Document
 *      'property'        => 'value_string',                // The property to compare value to
 *      'ignoreId'        => [ 123 ],                       // [Optional] IDs of records to be ignored
 * ];
 * $validator = new DocumentNotExists($params);
 *
 * 'ignoreId' is useful when editing entity, skip this option when creating.
 *
 * @category    Application
 * @package     Validator
 */
class DocumentNotExists extends AbstractValidator
{
    /**
     * @const DOCUMENT_EXISTS     When Document found
     */
    const DOCUMENT_EXISTS = 'documentExists';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::DOCUMENT_EXISTS => "Value is already in the database"
    );

    /**
     * Document manager
     *
     * @var DocumentManager
     */
    protected $dm;

    /**
     * Full Document name
     *
     * @var string
     */
    protected $document;

    /**
     * Property of the document to be checked for equality to a value
     *
     * @var string
     */
    protected $property;

    /**
     * IDs of records to ignore
     *
     * @var integer|array
     */
    protected $ignoreId;

    /**
     * Set document manager
     *
     * @param DocumentManager $dm
     * @return DocumentNotExists
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;

        return $this;
    }

    /**
     * Get document manager
     *
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->dm;
    }

    /**
     * Set document name
     *
     * @param string $document
     * @return DocumentNotExists
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document name
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set property name
     *
     * @param string $property
     * @return DocumentNotExists
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get entity name
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set IDs to be ignored
     *
     * @oaram integer|array $id
     * @return DocumentNotExists
     */
    public function setIgnoreId($id)
    {
        $this->ignoreId = $id;

        return $this;
    }

    /**
     * Get ignored ID
     *
     * @return integer|array|null
     */
    public function getIgnoreId()
    {
        return $this->ignoreId;
    }

    /**
     * Returns true if $value is valid
     *
     * @param  mixed $value
     * @return boolean
     * @throws Exception    When not configured
     */
    public function isValid($value)
    {
        $this->setValue($value);

        $options = $this->getOptions();

        $dm = $this->getDocumentManager();
        if (!$dm)
            throw new Exception('No DocumentManager provided');

        $document = $this->getDocument();
        if (!$document)
            throw new Exception('No document name provided');

        $property = $this->getProperty();
        if (!$property)
            throw new Exception('No property name provided');

        $qb = $dm->createQueryBuilder();
        $qb->find($document)
           ->field($property)->equals($value);

        $ignore = $this->getIgnoreId();
        if ($ignore !== null) {
            if (!is_array($ignore))
                $ignore = [ $ignore ];
            $qb->field('id')->notIn($ignore);
        }

        $check = $qb->getQuery()->execute()->count();
        if ($check > 0) {
            $this->error(self::DOCUMENT_EXISTS);
            return false;
        }

        return true;
    }
}
