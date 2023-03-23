<?php

namespace Vonage\Verify2\VerifyObjects;

use InvalidArgumentException;

class VerifySilentAuthUpdate
{
    private static array $mandatoryFields = [
        'request_id',
        'triggered_at',
        'type',
        'channel',
        'status',
        'action'
    ];

    private string $requestId;
    private string $triggeredAt;
    private string $type;
    private string $channel;
    private string $status;
    private array $action;

    public function __construct(array $data)
    {
        foreach (static::$mandatoryFields as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException('Verify SilentAuth Update missing required data `' . $key . '`');
            }
        }

        $this->requestId = $data['request_id'];
        $this->triggeredAt = $data['triggered_at'];
        $this->type = $data['type'];
        $this->channel = $data['channel'];
        $this->status = $data['status'];
        $this->action = $data['action'];
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): VerifySilentAuthUpdate
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getTriggeredAt(): string
    {
        return $this->triggeredAt;
    }

    public function setTriggeredAt(string $triggeredAt): VerifySilentAuthUpdate
    {
        $this->triggeredAt = $triggeredAt;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): VerifySilentAuthUpdate
    {
        $this->type = $type;

        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): VerifySilentAuthUpdate
    {
        $this->channel = $channel;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): VerifySilentAuthUpdate
    {
        $this->status = $status;

        return $this;
    }

    public function getAction(): array
    {
        return $this->action;
    }

    public function setAction(array $action): VerifySilentAuthUpdate
    {
        $this->action = $action;

        return $this;
    }
}
