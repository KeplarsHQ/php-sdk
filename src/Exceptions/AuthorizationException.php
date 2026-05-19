<?php

namespace Keplars\Email\Exceptions;

class AuthorizationException extends KeplarsException
{
    public function __construct(string $message, ?string $requestId = null)
    {
        parent::__construct($message, 'AUTHORIZATION_ERROR', $requestId);
    }
}
