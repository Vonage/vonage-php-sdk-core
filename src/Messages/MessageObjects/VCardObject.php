<?php

namespace Vonage\Messages\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class VCardObject implements ArrayHydrateInterface
{
    public function __construct(private string $url)
    {
    }

    public function fromArray(array $data): VCardObject
    {
        $this->url = $data['url'];

        return $this;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url
        ];
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
