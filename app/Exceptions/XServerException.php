<?php

namespace App\Exceptions;

class XServerException extends \RuntimeException
{
    protected $code = 500;

    protected $message = 'Internal Server Error';


}
