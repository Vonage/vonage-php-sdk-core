<?php

declare(strict_types=1);

namespace Vonage\Voice\Webhook;

use DateTimeImmutable;
use Exception;

class Transfer
{
    protected ?string $conversationUuidFrom = null;

    protected ?string $conversationUuidTo = null;

    protected ?string $uuid = null;

    protected ?DateTimeImmutable $timestamp = null;

    /**
     * @throws Exception
     */
    public function __construct(array $event)
    {
        $this->conversationUuidFrom = $event['conversation_uuid_from'];
        $this->conversationUuidTo = $event['conversation_uuid_to'];
        $this->uuid = $event['uuid'];
        $this->timestamp = new DateTimeImmutable($event['timestamp']);
    }

    public function getConversationUuidFrom(): string
    {
        return $this->conversationUuidFrom;
    }

    public function getConversationUuidTo(): string
    {
        return $this->conversationUuidTo;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
