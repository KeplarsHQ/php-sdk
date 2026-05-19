<?php

namespace Keplars\Email\Exceptions;

class DomainNotVerifiedException extends KeplarsException
{
    protected string $domain;
    protected string $verificationStatus;

    public function __construct(
        string $message,
        string $domain,
        string $verificationStatus,
        ?string $requestId = null
    ) {
        parent::__construct($message, 'DOMAIN_NOT_VERIFIED', $requestId);
        $this->domain = $domain;
        $this->verificationStatus = $verificationStatus;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getVerificationStatus(): string
    {
        return $this->verificationStatus;
    }

    public function __toString(): string
    {
        return parent::__toString() . " (domain: {$this->domain}, status: {$this->verificationStatus})";
    }
}
