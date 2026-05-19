<?php

namespace Keplars\Email\Exceptions;

use Exception;

class NetworkException extends KeplarsException
{
    public function __construct(string $message, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, 'NETWORK_ERROR', null, $previous);
    }
}
