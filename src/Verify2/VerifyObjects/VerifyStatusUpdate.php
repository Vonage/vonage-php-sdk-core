<?php

namespace Vonage\Verify2\VerifyObjects;

use InvalidArgumentException;

class VerifyStatusUpdate
{
    private static array $mandatoryFields = [
        'request_id',
        'submitted_at',
        'status',
        'type',
        'channel_timeout',
        'workflow',
        'price',
        'client_ref'
    ];
    private string $requestId;
    private string $submittedAt;
    private string $status;
    private string $type;
    private string $channelTimeout;
    private array $workflow;
    private string $price;
    private string $clientRef;

    public function __construct(array $data)
    {
        foreach (static::$mandatoryFields as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException('Verify Status Update missing required data `' . $key . '`');
            }
        }

        $this->requestId = $data['request_id'];
        $this->submittedAt = $data['submitted_at'];
        $this->status = $data['status'];
        $this->type = $data['type'];
        $this->channelTimeout = $data['channel_timeout'];
        $this->workflow = $data['workflow'];
        $this->price = $data['price'];
        $this->clientRef = $data['client_ref'];
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): VerifyStatusUpdate
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getSubmittedAt(): string
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(string $submittedAt): VerifyStatusUpdate
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): VerifyStatusUpdate
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): VerifyStatusUpdate
    {
        $this->type = $type;

        return $this;
    }

    public function getChannelTimeout(): string
    {
        return $this->channelTimeout;
    }

    public function setChannelTimeout(string $channelTimeout): VerifyStatusUpdate
    {
        $this->channelTimeout = $channelTimeout;

        return $this;
    }

    public function getWorkflow(): array
    {
        return $this->workflow;
    }

    public function setWorkflow(array $workflow): VerifyStatusUpdate
    {
        $this->workflow = $workflow;

        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): VerifyStatusUpdate
    {
        $this->price = $price;

        return $this;
    }

    public function getClientRef(): string
    {
        return $this->clientRef;
    }

    public function setClientRef(string $clientRef): VerifyStatusUpdate
    {
        $this->clientRef = $clientRef;

        return $this;
    }
}
