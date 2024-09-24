<?php

declare(strict_types=1);

namespace Vonage\Client\Credentials;

/**
 * Class Basic
 * Read-only container for api key and secret.
 */
class Basic extends AbstractCredentials
{
    /**
     * Create a credential set with an API key and secret.
     */
    public function __construct($key, $secret)
    {
        $this->credentials['api_key'] = (string)$key;
        $this->credentials['api_secret'] = (string)$secret;
    }
}
