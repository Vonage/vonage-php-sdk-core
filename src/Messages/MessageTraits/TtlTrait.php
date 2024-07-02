<?php

namespace Vonage\Messages\MessageTraits;

trait TtlTrait
{
    private ?int $ttl;

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setTTl(?int $ttl): static
    {
        $this->ttl = $ttl;

        return $this;
    }
}
