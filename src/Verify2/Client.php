<?php

namespace Vonage\Verify2;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Exception\Request;
use Vonage\Verify2\Request\BaseVerifyRequest;
use Vonage\Verify2\Request\SilentAuthRequest;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

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
        if (self::isSilentAuthRequest($request)) {
            if (SilentAuthRequest::isValidWorkflow($request->getWorkflows())) {
                return $this->getAPIResource()->create($request->toArray());
            }

            throw new \InvalidArgumentException('Silent Auth must be the first workflow if used');
        }

        return $this->getAPIResource()->create($request->toArray());
    }

    public function check(string $requestId, $code): bool
    {
        try {
            $response = $this->getAPIResource()->create(['code' => $code], '/' . $requestId);
        } catch (Exception $e) {
            // For horrible reasons in the API Error Handler, throw the error unless it's a 409.
            if ($e->getCode() === 409) {
                throw new Request('Conflict: The current Verify workflow step does not support a code.');
            }

            throw $e;
        }

        return true;
    }

    public function cancelRequest(string $requestId): bool
    {
        $this->api->delete($requestId);

        return true;
    }

    public function nextWorkflow(string $requestId): bool
    {
        $this->api->create([], '/' . $requestId . '/next_workflow');

        return true;
    }

    public static function isSilentAuthRequest(BaseVerifyRequest $request): bool
    {
        foreach ($request->getWorkflows() as $workflow) {
            if ($workflow['channel'] == VerificationWorkflow::WORKFLOW_SILENT_AUTH) {
                return true;
            }
        }

        return false;
    }
}
