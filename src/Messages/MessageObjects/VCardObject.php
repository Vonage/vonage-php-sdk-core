<?php

namespace Vonage\Messages\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class VCardObject implements ArrayHydrateInterface
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
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
