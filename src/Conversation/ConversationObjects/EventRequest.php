<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class EventRequest implements ArrayHydrateInterface
{
    public function __construct(
        protected string $conversationId,
        protected string $eventType,
        protected string $from,
        protected array $body
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getEventType(),
            'from' => $this->getFrom(),
            'body' => $this->getBody()
        ];
    }

    public function fromArray(array $data): EventRequest
    {
        $this->from = $data['from'];
        $this->eventType = $data['type'];
        $this->body = $data['body'];

        return $this;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): EventRequest
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): EventRequest
    {
        $this->from = $from;

        return $this;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setBody(array $body): EventRequest
    {
        $this->body = $body;

        return $this;
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    public function setConversationId(string $conversationId): EventRequest
    {
        $this->conversationId = $conversationId;

        return $this;
    }
}
