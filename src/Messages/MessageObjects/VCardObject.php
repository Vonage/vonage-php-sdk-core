<?php

namespace Vonage\Messages\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class VCardObject implements ArrayHydrateInterface
{
    public function __construct(
        private string $url,
        private ?string $caption = null
    ) {
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

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): void
    {
        $this->caption = $caption;
    }
}
