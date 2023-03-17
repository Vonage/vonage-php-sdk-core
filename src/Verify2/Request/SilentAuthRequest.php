<?php

namespace Vonage\Verify2\Request;

use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class SilentAuthRequest extends BaseVerifyRequest
{
    public function __construct(
        protected string $to,
        protected string $brand,
    ) {
        $workflow = new VerificationWorkflow('silent_auth', $to);
        $this->addWorkflow($workflow);
    }

    public function toArray(): array
    {
        return [
            'brand' => $this->getBrand(),
            'workflow' => $this->getWorkflows()
        ];
    }
}