<?php

namespace Keplars\Email\Exceptions;

class InternalException extends KeplarsException
{
    public function __construct(string $message, ?string $requestId = null)
    {
        parent::__construct($message, 'INTERNAL_ERROR', $requestId);
    }
}
