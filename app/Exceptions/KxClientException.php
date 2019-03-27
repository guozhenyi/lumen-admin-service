<?php

namespace App\Exceptions;

class KxClientException extends \RuntimeException
{
    protected $code = 400;

    protected $message = 'Bad Request';


}
