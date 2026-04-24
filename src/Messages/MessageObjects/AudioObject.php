<?php

namespace Vonage\Messages\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class AudioObject implements ArrayHydrateInterface
{
    /**
     * Legacy to pass in a caption as this should never have been supported. Nothing will happen if you pass one in.
     */
    public function __construct(private string $url, private readonly ?string $caption = null)
    {
    }

    public function fromArray(array $data): AudioObject
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
