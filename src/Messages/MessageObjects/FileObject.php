<?php

namespace Vonage\Messages\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class FileObject implements ArrayHydrateInterface
{
    public function __construct(
        private string $url,
        private string $caption = '',
        private ?string $name = null,
    ) {
    }

    public function fromArray(array $data): FileObject
    {
        $this->url = $data['url'];

        if (isset($data['caption'])) {
            $this->caption = $data['caption'];
        }

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        return $this;
    }

    public function toArray(): array
    {
        $returnArray = [
            'url' => $this->url
        ];

        if ($this->getCaption()) {
            $returnArray['caption'] = $this->getCaption();
        }

        if ($this->getName()) {
            $returnArray['name'] = $this->getName();
        }

        return $returnArray;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
