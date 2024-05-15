<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects\Events;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

abstract class BaseEvent implements ArrayHydrateInterface
{
    protected string $conversationId;

    public function __construct(protected string $eventType, protected string $from, protected array $body)
    {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getEventType(),
            'from' => $this->getFrom(),
            'body' => $this->getBody()
        ];
    }

    public function fromArray(array $data): BaseEvent
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

    public function setEventType(string $eventType): BaseEvent
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): BaseEvent
    {
        $this->from = $from;

        return $this;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setBody(array $body): BaseEvent
    {
        $this->body = $body;

        return $this;
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    public function setConversationId(string $conversationId): BaseEvent
    {
        $this->conversationId = $conversationId;

        return $this;
    }


}
