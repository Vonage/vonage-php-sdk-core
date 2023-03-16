<?php

namespace Vonage\Verify2;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Verify2\Request\BaseVerifyRequest;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function send(BaseVerifyRequest $request): ?array
    {
        return $this->getAPIResource()->create($request->toArray());
    }
}