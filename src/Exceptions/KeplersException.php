<?php

namespace Keplars\Email\Exceptions;

use Exception;

class KeplarsException extends Exception
{
    protected string $code;
    protected ?string $requestId;

    public function __construct(
        string $message,
        string $code = 'UNKNOWN_ERROR',
        ?string $requestId = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->code = $code;
        $this->requestId = $requestId;
    }

    public function getErrorCode(): string
    {
        return $this->code;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function __toString(): string
    {
        $str = $this->message . " (code: {$this->code})";
        if ($this->requestId) {
            $str .= " (request_id: {$this->requestId})";
        }
        return $str;
    }
}
