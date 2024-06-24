<?php

declare(strict_types=1);

namespace Vonage\Client\Credentials;

class Gnp extends Keypair
{
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
}
