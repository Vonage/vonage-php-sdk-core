<?php

namespace Vonage\Messages\Channel\Viber\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class ViberActionObject implements ArrayHydrateInterface
{
    public function __construct(private string $url, private string $text = '')
    {
    }

    public function fromArray(array $data): ViberActionObject
    {
        $this->url = $data['url'];
        $this->text = $data['text'];

        return $this;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->getUrl(),
            'text' => $this->getText()
        ];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
