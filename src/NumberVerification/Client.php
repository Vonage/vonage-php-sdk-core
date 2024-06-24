<?php

declare(strict_types=1);

namespace Vonage\NumberVerification;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function verify(): bool
    {
        return false;
    }
}
