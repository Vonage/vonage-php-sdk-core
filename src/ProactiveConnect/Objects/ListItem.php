<?php

namespace Vonage\ProactiveConnect\Objects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class ListItem implements ArrayHydrateInterface
{
    public function __construct(protected array $data)
    {
    }

    public function set($name, $value): static
    {
        $this->data[$name] = $value;

        return $this;
    }

    public function get(string $name)
    {
        return $this->data[$name];
    }

    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data
        ];
    }
}
