<?php

namespace Vonage\Messages\Channel\Viber;

trait ViberServiceObjectTrait
{
    private ?string $category;
    private ?int $ttl;
    private ?string $type;

    public function requiresViberServiceObject(): bool
    {
        return $this->getCategory() || $this->getTtl() || $this->getType();
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     */
    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    /**
     * @return int|null
     */
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

    /**
     * @return string|null
     */
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
