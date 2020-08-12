<?php
declare(strict_types=1);

namespace Vonage\Voice\Webhook;

class Notification
{
    /**
     * @var array<string, mixed>
     */
    protected $payload;

    /**
     * @var string
     */
    protected $conversationUuid;
    
    /**
     * @var \DateTimeImmutable
     */
    protected $timestamp;

    public function __construct(array $data)
    {
        if (is_string($data['payload'])) {
            $data['payload'] = json_decode($data['payload'], true);
        }

        $this->payload = $data['payload'];
        $this->conversationUuid = $data['conversation_uuid'];
        $this->timestamp = new \DateTimeImmutable($data['timestamp']);
    }

    public function getPayload() : array
    {
        return $this->payload;
    }

    public function getConversationUuid() : string
    {
        return $this->conversationUuid;
    }

    public function getTimestamp() : \DateTimeImmutable
    {
        return $this->timestamp;
    }
}
