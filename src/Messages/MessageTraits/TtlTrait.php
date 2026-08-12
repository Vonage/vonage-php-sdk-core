<?php

declare(strict_types=1);

namespace Vonage\Messages\MessageTraits;

trait TtlTrait
{
    private ?int $ttl = null;

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
