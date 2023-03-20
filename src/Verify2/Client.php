<?php

namespace Vonage\Verify2;

use Laminas\Diactoros\Request;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception;
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

    public function startVerification(BaseVerifyRequest $request): ?array
    {
        return $this->getAPIResource()->create($request->toArray());
    }

    public function check(string $requestId, $code): bool
    {
        try {
            $response = $this->getAPIResource()->create(['code' => $code], $requestId);
        } catch (Exception $e) {
            // For horrible reasons in the API Error Handler, throw the error unless it's a 409.
            if ($e->getCode() === 409) {
                throw new \Vonage\Client\Exception\Request('Conflict: The current Verify workflow step does not support a code.');
            }

            throw $e;
        }

        return true;
    }
}