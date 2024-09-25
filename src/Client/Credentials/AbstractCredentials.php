<?php

declare(strict_types=1);

namespace Vonage\Client\Credentials;

abstract class AbstractCredentials implements CredentialsInterface
{
    protected array $credentials = [];

    /**
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        return $this->credentials[$name];
    }

    public function asArray(): array
    {
        return $this->credentials;
    }
}
