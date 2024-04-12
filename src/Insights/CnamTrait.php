<?php

declare(strict_types=1);

namespace Vonage\Insights;

trait CnamTrait
{

    public function getCallerName(): ?string
    {
        return $this->data['caller_name'] ?? null;
    }

    public function getFirstName(): ?string
    {
        return $this->data['first_name'] ?? null;
    }

    public function getLastName(): ?string
    {
        return $this->data['last_name'] ?? null;
    }

    public function getCallerType(): ?string
    {
        return $this->data['caller_type'] ?? null;
    }
}
