<?php

namespace App\Exceptions;

class XClientException extends \RuntimeException
{
    protected $code = 400;

    protected $message = 'Bad Request';


}
