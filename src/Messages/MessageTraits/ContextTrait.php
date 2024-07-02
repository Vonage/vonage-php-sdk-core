<?php

namespace Vonage\Messages\MessageTraits;

trait ContextTrait
{
    private ?array $context;

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): static
    {
        $this->context = $context;

        return $this;
    }
}
