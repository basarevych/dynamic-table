<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Tool;

/**
 * Text helper class
 * 
 * @category    Application
 * @package     Tool
 */
class Number
{
    /**
     * Convert number to locale string
     *
     * @param   integer $number
     * @return  string
     */
    public static function localeFormat($number)
    {
        if ($number === null)
            return '';

        $fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
        $fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 6);
        return $fmt->format($number);
    }
} 
