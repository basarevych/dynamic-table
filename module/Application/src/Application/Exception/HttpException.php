<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Exception;

use Exception;

/**
 * Generic exception for raising HTTP errors
 *
 * @category    Application
 * @package     Exception
 */
class HttpException extends Exception
{
    /**
     * Constructor
     *
     * @param string    $message
     * @param integer   $code
     * @param Exception $prev
     */
    public function __construct($message, $code = 500, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}
