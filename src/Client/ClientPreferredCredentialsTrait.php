<?php

namespace Vonage\Client;

use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\OAuth;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Exception\Credentials;

trait ClientPreferredCredentialsTrait
{
    protected string $preferredCredentialsClass;

    protected array $allowedCredentialTypes = [
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
        if (!in_array($preferredCredentialsClass, $this->allowedCredentialTypes, true)) {
            throw new Credentials(
                'Attempting to add unknown credentials type in ' . __CLASS__ . ' ' . __LINE__
            );
        }

        $this->preferredCredentialsClass = $preferredCredentialsClass;
    }
}
