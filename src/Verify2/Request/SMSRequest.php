<?php

namespace Vonage\Verify2\Request;

use InvalidArgumentException;
use Vonage\Verify2\Traits\CustomTemplateTrait;
use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class SMSRequest extends BaseVerifyRequest
{
    use CustomTemplateTrait;

    public function __construct(
        protected string $to,
        protected string $brand,
        protected ?VerificationLocale $locale = null,
        protected string $from = '',
        protected string $entityId = '',
        protected string $contentId = ''
    ) {
        if (!self::isBrandValid($this->brand)) {
            throw new InvalidArgumentException('The brand name cannot be longer than 16 characters.');
        }

        if (!$this->locale) {
            $this->locale = new VerificationLocale();
        }

        $customKeys = array_filter([
            'entity_id' => $this->entityId,
            'content_id' => $this->contentId
        ]);

        $workflow = new VerificationWorkflow(VerificationWorkflow::WORKFLOW_SMS, $to, $from, $customKeys);

        $this->addWorkflow($workflow);
    }

    public function toArray(): array
    {
        $return = $this->getBaseVerifyUniversalOutputArray();

        if (!is_null($this->getTemplateId())) {
            $return['template_id'] = $this->getTemplateId();
        }

        return $return;
    }
}
