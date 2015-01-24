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
use Locale;
use NumberFormatter;
use Zend\Validator\AbstractValidator;

/**
 * Locale-aware integer number validator
 *
 * @category    Application
 * @package     Validator
 */
class Integer extends AbstractValidator
{
    /**
     * @const NOT_INTEGER
     */
    const NOT_INTEGER = 'notInteger';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_INTEGER => "Value is not an integer number"
    );

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

        $fmt = new NumberFormatter(Locale::getDefault(), NumberFormatter::DECIMAL);
        $parse = $fmt->parse($value, NumberFormatter::TYPE_INT64);
        $symbol = $fmt->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        if ($parse === false || strpos($value, $symbol) !== false) {
            $this->error(self::NOT_INTEGER);
            return false;
        }

        return true;
    }
}
