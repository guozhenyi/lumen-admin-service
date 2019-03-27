<?php

namespace App\Exceptions;

class KxTokenExpiredException extends \RuntimeException
{
    protected $code = 401;

    protected $message = 'Token Expired';


}
