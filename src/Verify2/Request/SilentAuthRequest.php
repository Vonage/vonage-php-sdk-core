<?php

namespace Vonage\Verify2\Request;

use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class SilentAuthRequest extends BaseVerifyRequest
{
    public function __construct(
        protected string $to,
        protected string $brand,
        protected ?string $redirectUrl = null
    ) {
        $workflow = new VerificationWorkflow(VerificationWorkflow::WORKFLOW_SILENT_AUTH, $to);

        if ($this->redirectUrl) {
            $workflow->setCustomKeys(['redirect_url' => $this->redirectUrl]);
        }

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
