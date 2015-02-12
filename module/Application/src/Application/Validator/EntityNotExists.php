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
use Doctrine\ORM\EntityManager;

/**
 * EntityNotExists validator
 *
 * Usage:
 *
 * $params = [
 *      'entityManager' => $em,                         // Doctrine EntityManager instance
 *      'entity'        => 'Application\Entity\Sample', // The Entity
 *      'property'      => 'value_string',              // The property to compare value to
 *      'ignoreId'      => [ 123 ],                     // [Optional] IDs of records to be ignored
 * ];
 * $validator = new EntityNotExists($params);
 *
 * 'ignoreId' is useful when editing entity, skip this option when creating.
 *
 * @category    Application
 * @package     Validator
 */
class EntityNotExists extends AbstractValidator
{
    /**
     * @const ENTITY_EXISTS     When entity found
     */
    const ENTITY_EXISTS = 'entityExists';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::ENTITY_EXISTS => "Value is already in the database"
    );

    /**
     * Entity manager
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * Full Entity name
     *
     * @var string
     */
    protected $entity;

    /**
     * Property of the entity to be checked for equality to a value
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
     * Set entity manager
     *
     * @param EntityManager $em
     * @return EntityNotExists
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * Get entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Set entity name
     *
     * @param string $entity
     * @return EntityManager
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get entity name
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set property name
     *
     * @param string $property
     * @return EntityNotExists
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
     * @return EntityNotExists
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

        $em = $this->getEntityManager();
        if (!$em)
            throw new Exception('No EntityManager provided');

        $entity = $this->getEntity();
        if (!$entity)
            throw new Exception('No entity name provided');

        $property = $this->getProperty();
        if (!$property)
            throw new Exception('No property name provided');

        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(e)')
           ->from($entity, 'e')
           ->where("e.$property = :value")
           ->setParameter('value', $value);

        $ignore = $this->getIgnoreId();
        if ($ignore !== null) {
            if (!is_array($ignore))
                $ignore = [ $ignore ];
            $qb->andWhere('e.id NOT IN (:id)')
               ->setParameter('id', $ignore);
        }

        $check = $qb->getQuery()->getSingleScalarResult();
        if ($check > 0) {
            $this->error(self::ENTITY_EXISTS);
            return false;
        }

        return true;
    }
}
