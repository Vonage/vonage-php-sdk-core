<?php

namespace Vonage\Verify2\Request;

use InvalidArgumentException;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class SilentAuthRequest extends BaseVerifyRequest
{
    public function __construct(
        protected string $to,
        protected string $brand,
        protected ?string $redirectUrl = null
    ) {
        if (!self::isBrandValid($this->brand)) {
            throw new InvalidArgumentException('The brand name cannot be longer than 16 characters.');
        }

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

    public static function isValidWorkflow(array $workflows): bool
    {
        $firstWorkflow = $workflows[0];

        if ($firstWorkflow['channel'] == VerificationWorkflow::WORKFLOW_SILENT_AUTH) {
            return true;
        }

        return false;
    }
}
