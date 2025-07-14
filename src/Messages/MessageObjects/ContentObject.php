<?php

namespace Vonage\Messages\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class ContentObject implements ArrayHydrateInterface
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';
    public const TYPE_VCARD = 'vcard';
    public const TYPE_FILE = 'file';

    public function __construct(
        private string $url,
        private string $caption = '',
        private string $type = self::TYPE_IMAGE
            | self::TYPE_AUDIO
            | self::TYPE_VIDEO
            | self:: TYPE_VCARD
            | self::TYPE_FILE,
    ) {
    }

    public function fromArray(array $data): ContentObject
    {
        $this->url = $data['url'];
        $this->type = $data['type'];

        if (isset($data['caption'])) {
            $this->caption = $data['caption'];
        }

        return $this;
    }

    public function toArray(): array
    {
        $returnArray = [
            'url' => $this->url,
            'type' => $this->type,
        ];

        if ($this->getCaption()) {
            $returnArray['caption'] = $this->getCaption();
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

    public function getType(): string
    {
        return $this->type;
    }
}
