<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects\Events\Message;

trait MessageEventTrait
{
    protected string $eventType = 'message';

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }
}
