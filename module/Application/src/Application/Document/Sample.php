<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Sample document
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ODM\Document(repositoryClass="Application\Document\SampleRepository")
 */
class Sample 
{
    /**
     * Object ID
     *
     * @var integer
     * 
     * @ODM\Id
     */
    protected $id;

    /**
     * String value
     *
     * @var string
     * 
     * @ODM\String
     */
    protected $value_string;

    /**
     * Integer value
     *
     * @var integer
     * 
     * @ODM\Int
     */
    protected $value_integer;

    /**
     * Float value
     *
     * @var float
     * 
     * @ODM\Float
     */
    protected $value_float;

    /**
     * Boolean value
     *
     * @var boolean
     * 
     * @ODM\Boolean
     */
    protected $value_boolean;

    /**
     * DateTime value
     *
     * @var DateTime
     * 
     * @ODM\Date
     */
    protected $value_datetime;

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $dt = $this->getValueDatetime();
        return array(
            'id'                => $this->getId(),
            'value_string'      => $this->getValueString(),
            'value_integer'     => $this->getValueInteger(),
            'value_float'       => $this->getValueFloat(),
            'value_boolean'     => $this->getValueBoolean(),
            'value_datetime'    => $dt ? $dt->getTimestamp() : null
        );
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set valueString
     *
     * @param string $valueString
     * @return self
     */
    public function setValueString($valueString)
    {
        $this->value_string = $valueString;
        return $this;
    }

    /**
     * Get valueString
     *
     * @return string $valueString
     */
    public function getValueString()
    {
        return $this->value_string;
    }

    /**
     * Set valueInteger
     *
     * @param int $valueInteger
     * @return self
     */
    public function setValueInteger($valueInteger)
    {
        $this->value_integer = $valueInteger;
        return $this;
    }

    /**
     * Get valueInteger
     *
     * @return int $valueInteger
     */
    public function getValueInteger()
    {
        return $this->value_integer;
    }

    /**
     * Set valueFloat
     *
     * @param float $valueFloat
     * @return self
     */
    public function setValueFloat($valueFloat)
    {
        $this->value_float = $valueFloat;
        return $this;
    }

    /**
     * Get valueFloat
     *
     * @return float $valueFloat
     */
    public function getValueFloat()
    {
        return $this->value_float;
    }

    /**
     * Set valueBoolean
     *
     * @param boolean $valueBoolean
     * @return self
     */
    public function setValueBoolean($valueBoolean)
    {
        $this->value_boolean = $valueBoolean;
        return $this;
    }

    /**
     * Get valueBoolean
     *
     * @return boolean $valueBoolean
     */
    public function getValueBoolean()
    {
        return $this->value_boolean;
    }

    /**
     * Set valueDatetime
     *
     * @param date $valueDatetime
     * @return self
     */
    public function setValueDatetime($valueDatetime)
    {
        $this->value_datetime = $valueDatetime;
        return $this;
    }

    /**
     * Get valueDatetime
     *
     * @return date $valueDatetime
     */
    public function getValueDatetime()
    {
        return $this->value_datetime;
    }
}
