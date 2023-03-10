<?php

namespace Vonage\Messages\Channel\Viber;

use Vonage\Messages\Channel\Viber\MessageObjects\ViberActionObject;

trait ViberServiceObjectTrait
{
    private ?string $category = null;
    private ?int $ttl = null;
    private ?string $type = null;
    private ?ViberActionObject $action = null;

    public function requiresViberServiceObject(): bool
    {
        return $this->getCategory() || $this->getTtl() || $this->getType() || $this->getAction();
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setTtl(int $ttl): static
    {
        $this->ttl = $ttl;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAction(): ?ViberActionObject
    {
        return $this->action;
    }

    public function setAction(ViberActionObject $viberActionObject): static
    {
        $this->action = $viberActionObject;

        return $this;
    }
}
