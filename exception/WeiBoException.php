<?php
/**
 * Created by PhpStorm.
 * User: youthlin.chen
 * Date: 2017/9/7
 * Time: 21:28
 */

namespace Lin;


use Throwable;

class WeiBoException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
