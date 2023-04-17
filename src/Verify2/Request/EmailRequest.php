<?php

namespace Vonage\Verify2\Request;

use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class EmailRequest extends BaseVerifyRequest
{
    public function __construct(
        protected string $to,
        protected string $brand,
        protected string $from,
        protected ?VerificationLocale $locale = null,
    ) {
        if (!$this->locale) {
            $this->locale = new VerificationLocale();
        }

        if ($this->code) {
            $this->setCode($this->code);
        }

        $workflow = new VerificationWorkflow(VerificationWorkflow::WORKFLOW_EMAIL, $to, $from);

        $this->addWorkflow($workflow);
    }

    public function toArray(): array
    {
        return $this->getBaseVerifyUniversalOutputArray();
    }
}
