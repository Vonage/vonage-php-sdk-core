<?php

namespace Vonage\Meetings;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class Room implements ArrayHydrateInterface
{
    protected array $data;

    public function fromArray(array $data): static
    {
        if (!isset($data['display_name'])) {
            throw new \InvalidArgumentException('A room object must contain a display_name');
        }

        $this->data = $data;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter($this->data, static fn ($value) => $value !== '');
    }

    public function __get($value)
    {
        return $this->data[$value];
    }
}
