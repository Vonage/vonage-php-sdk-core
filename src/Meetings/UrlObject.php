<?php

namespace Vonage\Meetings;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class UrlObject implements ArrayHydrateInterface
{
    private array $data;

    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }
}
