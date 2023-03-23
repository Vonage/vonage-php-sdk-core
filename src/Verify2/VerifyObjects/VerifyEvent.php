<?php

namespace Vonage\Verify2\VerifyObjects;

use InvalidArgumentException;

class VerifyEvent
{
    private static array $mandatoryFields = [
        'request_id',
        'triggered_at',
        'type',
        'channel',
        'status',
        'finalized_at',
        'client_ref'
    ];
    private string $requestId;
    private string $triggeredAt;
    private string $type;
    private string $channel;
    private string $status;
    private string $finalizedAt;
    private string $clientRef;

    public function __construct(array $data)
    {
        foreach (static::$mandatoryFields as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException('Verify Event missing required data `' . $key . '`');
            }
        }

        $this->requestId = $data['request_id'];
        $this->triggeredAt = $data['triggered_at'];
        $this->type = $data['type'];
        $this->channel = $data['channel'];
        $this->status = $data['status'];
        $this->finalizedAt = $data['finalized_at'];
        $this->clientRef = $data['client_ref'];
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): VerifyEvent
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getTriggeredAt(): string
    {
        return $this->triggeredAt;
    }

    public function setTriggeredAt(string $triggeredAt): VerifyEvent
    {
        $this->triggeredAt = $triggeredAt;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): VerifyEvent
    {
        $this->type = $type;

        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): VerifyEvent
    {
        $this->channel = $channel;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): VerifyEvent
    {
        $this->status = $status;

        return $this;
    }

    public function getFinalizedAt(): string
    {
        return $this->finalizedAt;
    }

    public function setFinalizedAt(string $finalizedAt): VerifyEvent
    {
        $this->finalizedAt = $finalizedAt;

        return $this;
    }

    public function getClientRef(): string
    {
        return $this->clientRef;
    }

    public function setClientRef(string $clientRef): VerifyEvent
    {
        $this->clientRef = $clientRef;

        return $this;
    }
}
