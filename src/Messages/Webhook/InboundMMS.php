<?php

declare(strict_types=1);

namespace Vonage\Messages\Webhook;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

final class InboundMMS implements ArrayHydrateInterface
{
    protected ?array $data = null;

    public function fromArray(array $data): self
    {
        $this->data = $data;
        return $this;
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
