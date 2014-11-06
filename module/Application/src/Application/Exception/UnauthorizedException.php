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
 * HTTP 401 Unauthorized
 *
 * @category    Application
 * @package     Exception
 */
class UnauthorizedException extends HttpException
{
    /**
     * Constructor
     *
     * @param string    $message
     * @param integer   $code
     * @param Exception $prev
     */
    public function __construct($message = 'Authentication is required', $code = 401, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}
