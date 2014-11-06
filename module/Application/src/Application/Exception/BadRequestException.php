<?php
/**
 * AngularZF
 *
 * @link        https://github.com/basarevych/AngularZF
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Exception;

use Exception;

/**
 * HTTP 400 Bad Request
 *
 * @category    Application
 * @package     Exception
 */
class BadRequestException extends HttpException
{
    /**
     * Constructor
     *
     * @param string    $message
     * @param integer   $code
     * @param Exception $prev
     */
    public function __construct($message = 'Invalid parameters', $code = 400, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}
