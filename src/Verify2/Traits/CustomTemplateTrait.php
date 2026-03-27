<?php

declare(strict_types=1);

namespace Vonage\Verify2\Traits;

trait CustomTemplateTrait
{
    protected ?string $templateId = null;

    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    public function setTemplateId(string $templateId): string
    {
        return $this->templateId = $templateId;
    }
}
