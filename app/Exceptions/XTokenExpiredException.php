<?php

namespace App\Exceptions;

class XTokenExpiredException extends \RuntimeException
{
    protected $code = 1001;

    protected $message = 'Token Expired';


}
