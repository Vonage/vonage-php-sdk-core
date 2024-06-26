<?php

declare(strict_types=1);

namespace Vonage\Client\Credentials;

class Gnp extends Keypair
{
    protected ?string $code = null;
    protected ?string $state = null;
    protected ?string $redirectUri = null;

    public function __construct(protected string $msisdn, protected string $key, $application = null)
    {
        parent::__construct($key, $application);
    }

    public function getMsisdn(): string
    {
        return $this->msisdn;
    }

    public function setMsisdn(string $msisdn): Gnp
    {
        $this->msisdn = $msisdn;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): Gnp
    {
        $this->code = $code;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): Gnp
    {
        $this->state = $state;
        return $this;
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(?string $redirectUri): Gnp
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }
}
