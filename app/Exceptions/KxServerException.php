<?php

namespace App\Exceptions;

class KxServerException extends \RuntimeException
{
    protected $code = 500;

    protected $message = 'Internal Server Error';


}
