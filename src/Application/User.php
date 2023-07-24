<?php

namespace Vonage\Application;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class User implements ArrayHydrateInterface
{
    private array $data;

    public function __get($property)
    {
        return $this->data[$property] ?? null;
    }

    public function __set($property, $value)
    {
        $this->data[$property] = $value;

        return $this;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function fromArray(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
