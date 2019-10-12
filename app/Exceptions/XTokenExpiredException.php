<?php

namespace App\Exceptions;

class XTokenExpiredException extends \RuntimeException
{
    protected $code = 401;

    protected $message = 'Token Expired';


}
