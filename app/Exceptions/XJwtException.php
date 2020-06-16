<?php

namespace App\Exceptions;

class XJwtException extends \RuntimeException
{
    protected $code = 40101;

    protected $message = 'Jwt Exception';


}
