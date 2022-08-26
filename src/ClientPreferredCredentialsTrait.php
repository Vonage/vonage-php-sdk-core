<?php

namespace Vonage;

use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\OAuth;
use Vonage\Client\Credentials\SignatureSecret;

trait ClientPreferredCredentialsTrait
{
    public string $preferredCredentialsClass;

    public array $allowedCredentialTypes = [
        Basic::class,
        Keypair::class,
        OAuth::class,
        SignatureSecret::class
    ];

    public function getPreferredCredentialsClass(): string
    {
        return $this->preferredCredentialsClass;
    }

    public function setPreferredCredentialsClass(string $preferredCredentialsClass): void
    {
        $this->preferredCredentialsClass = $preferredCredentialsClass;
    }
}