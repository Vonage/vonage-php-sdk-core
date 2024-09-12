<?php

namespace Vonage\Verify2\Request;

use InvalidArgumentException;
use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class UpdateCustomTemplateRequest extends BaseVerifyRequest
{
    public function __construct(
        protected ?string $name = null,
        protected ?bool $isDefault = null,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): UpdateCustomTemplateRequest
    {
        $this->name = $name;
        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(?bool $isDefault): UpdateCustomTemplateRequest
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function toArray(): array
    {
        $return = [];

        if ($this->getName()) {
            $return['name'] = $this->getName();
        }

        if ($this->getIsDefault()) {
            $return['is_default'] = $this->getIsDefault();
        }

        return $return;
    }
}
