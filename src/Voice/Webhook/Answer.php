<?php

declare(strict_types=1);

namespace Vonage\Voice\Webhook;

class Answer
{
    protected ?string $conversationUuid = null;

    protected ?string $from = null;

    protected ?string $to = null;

    protected ?string $uuid = null;

    public function __construct(array $event)
    {
        $this->from = $event['from'];
        $this->to = $event['to'];
        $this->uuid = $event['uuid'] ?? $event['call_uuid'];
        $this->conversationUuid = $event['conversation_uuid'];
    }

    public function getConversationUuid(): string
    {
        return $this->conversationUuid;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
