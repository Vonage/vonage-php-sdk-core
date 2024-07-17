<?php

namespace Vonage\Verify2\Request;

use InvalidArgumentException;
use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class WhatsAppInteractiveRequest extends BaseVerifyRequest
{
    public function __construct(
        protected string $to,
        protected string $brand,
        protected ?VerificationLocale $locale = null
    ) {
        if (!self::isBrandValid($this->brand)) {
            throw new InvalidArgumentException('The brand name cannot be longer than 16 characters.');
        }

        if (!$this->locale) {
            $this->locale = new VerificationLocale();
        }

        $workflow = new VerificationWorkflow(VerificationWorkflow::WORKFLOW_WHATSAPP_INTERACTIVE, $to);
        $this->addWorkflow($workflow);
    }

    public function toArray(): array
    {
        return $this->getBaseVerifyUniversalOutputArray();
    }
}
