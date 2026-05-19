<?php

namespace Keplars\Email\Exceptions;

class AuthenticationException extends KeplarsException
{
    public function __construct(string $message, ?string $requestId = null)
    {
        parent::__construct($message, 'AUTHENTICATION_ERROR', $requestId);
    }
}
