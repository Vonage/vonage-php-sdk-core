<?php

namespace Vonage\Verify2\Request;

use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class SMSRequest extends BaseVerifyRequest
{
    public function __construct(
        protected string $to,
        protected string $brand,
        protected ?VerificationLocale $locale = null,
        protected string $from = '',
    ) {
        if (!$this->locale) {
            $this->locale = new VerificationLocale();
        }

        $workflow = new VerificationWorkflow(VerificationWorkflow::WORKFLOW_SMS, $to, $from);

        $this->addWorkflow($workflow);
    }

    public function toArray(): array
    {
        return $this->getBaseVerifyUniversalOutputArray();
    }
}
