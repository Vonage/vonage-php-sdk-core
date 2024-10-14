<?php

namespace Vonage\Messages\Channel\WhatsApp\MessageObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class StickerObject implements ArrayHydrateInterface
{
    public const STICKER_URL = 'url';
    public const STICKER_ID = 'id';

    private array $allowedTypes = [
        self::STICKER_URL,
        self::STICKER_ID
    ];

    public function __construct(private string $type, private string $value = '')
    {
        if (! in_array($type, $this->allowedTypes, true)) {
            throw new \InvalidArgumentException($type . ' is an invalid type of Sticker');
        }
    }

    public function fromArray(array $data): StickerObject
    {
        if (! in_array($data['type'], $this->allowedTypes, true)) {
            throw new \InvalidArgumentException($data['type'] . ' is an invalid type of Sticker');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [$this->getType() => $this->getValue()];
    }
}
