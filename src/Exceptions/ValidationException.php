<?php

namespace Keplars\Email\Exceptions;

class ValidationException extends KeplarsException
{
    protected ?array $details;

    public function __construct(string $message, ?array $details = null, ?string $requestId = null)
    {
        parent::__construct($message, 'VALIDATION_ERROR', $requestId);
        $this->details = $details;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function __toString(): string
    {
        $str = parent::__toString();
        if ($this->details) {
            $str .= "\nDetails: " . json_encode($this->details);
        }
        return $str;
    }
}
