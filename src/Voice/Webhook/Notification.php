<?php

declare(strict_types=1);

namespace Vonage\Voice\Webhook;

use DateTimeImmutable;
use Exception;

use function is_string;
use function json_decode;

class Notification
{
    /**
     * @var array<string, mixed>
     */
    protected ?array $payload = null;

    protected ?string $conversationUuid = null;

    protected ?DateTimeImmutable $timestamp = null;

    /**
     * @throws Exception
     */
    public function __construct(array $data)
    {
        if (is_string($data['payload'])) {
            $data['payload'] = json_decode($data['payload'], true);
        }

        $this->payload = $data['payload'];
        $this->conversationUuid = $data['conversation_uuid'];
        $this->timestamp = new DateTimeImmutable($data['timestamp']);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getConversationUuid(): string
    {
        return $this->conversationUuid;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
