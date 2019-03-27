<?php

namespace App\Exceptions;

class KxJwtException extends \RuntimeException
{
    protected $code = 40101;

    protected $message = 'Jwt Exception';


}
