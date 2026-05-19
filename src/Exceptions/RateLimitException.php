<?php

namespace Keplars\Email\Exceptions;

class RateLimitException extends KeplarsException
{
    protected int $retryAfter;

    public function __construct(string $message, int $retryAfter, ?string $requestId = null)
    {
        parent::__construct($message, 'RATE_LIMIT_EXCEEDED', $requestId);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    public function __toString(): string
    {
        return parent::__toString() . " (retry_after: {$this->retryAfter}s)";
    }
}
