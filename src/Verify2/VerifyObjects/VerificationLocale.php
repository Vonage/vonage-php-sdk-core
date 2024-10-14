<?php

namespace Vonage\Verify2\VerifyObjects;

class VerificationLocale
{
    public function __construct(protected string $code = 'en-us')
    {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }
}
