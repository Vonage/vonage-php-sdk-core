<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class ConversationNumber implements ArrayHydrateInterface
{
    public const PHONE_TYPE = 'phone';

    protected string $type = self::PHONE_TYPE;

    public function __construct(
        protected ?string $number,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): ConversationNumber
    {
        $this->number = $number;

        return $this;
    }

    public function fromArray(array $data): static
    {
        if (isset($data['number'])) {
            $this->number = $data['number'];
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'number' => $this->getNumber()
        ];
    }
}
