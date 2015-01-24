<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Validator;

use Exception;
use Zend\Validator\AbstractValidator;

/**
 * Two fields must have the same value
 *
 * @category    Application
 * @package     Validator
 */
class ValuesMatch extends AbstractValidator
{
    /**
     * @const NOT_EQUAL         When fields are different
     * @const CANNOT_COMPARE    The other field was not found in $context
     */
    const NOT_EQUAL = 'notEqual';
    const CANNOT_COMPARE = 'cannotCompare';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_EQUAL => "The two values do not match",
        self::CANNOT_COMPARE => "Could not compare provided fields"
    );

    /**
     * Name of the other field
     *
     * @var string
     */
    protected $compareTo = null;

    /**
     * Set other field name
     *
     * @param string $compareTo
     * @return ValuesMatch
     */
    public function setCompareTo($compareTo)
    {
        $this->compareTo = $compareTo;

        return $this;
    }

    /**
     * Get other field name
     *
     * @return string|null
     */
    public function getCompareTo()
    {
        return $this->compareTo;
    }

    /**
     * Returns true if $value was correcty retyped
     *
     * @param  mixed $value
     * @return boolean
     * @throws Exception    When not configured
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $compareTo = $this->getCompareTo();
        if (!$compareTo)
            throw new Exception('Parameter compareTo was not provided');

        if (!isset($context[$compareTo])) {
            $this->error(self::CANNOT_COMPARE);
            return false;
        }

        if ($value != @$context[$compareTo]) {
            $this->error(self::NOT_EQUAL);
            return false;
        }

        return true;
    }
}
