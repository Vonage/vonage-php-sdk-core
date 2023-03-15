<?php

namespace Vonage\Verify2;

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
}