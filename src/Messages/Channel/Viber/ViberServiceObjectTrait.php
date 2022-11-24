<?php

namespace Vonage\Messages\Channel\Viber;

trait ViberServiceObjectTrait
{
    private ?string $category = null;
    private ?int $ttl = null;
    private ?string $type = null;

    public function requiresViberServiceObject(): bool
    {
        return $this->getCategory() || $this->getTtl() || $this->getType();
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    /**
     * @param int|null $ttl
     */
    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
