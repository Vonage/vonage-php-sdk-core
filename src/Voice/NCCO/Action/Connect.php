<?php

declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use InvalidArgumentException;
use Vonage\Voice\Endpoint\EndpointInterface;
use Vonage\Voice\VoiceObjects\AdvancedMachineDetection;
use Vonage\Voice\Webhook;

class Connect implements ActionInterface
{
    public const EVENT_TYPE_SYNCHRONOUS = 'synchronous';
    public const MACHINE_CONTINUE = 'continue';
    public const MACHINE_HANGUP = 'hangup';

    protected ?string $from = '';
    protected ?string $eventType = '';

    protected int $timeout = 0;
    protected int $limit = 0;
    protected string $machineDetection = '';
    protected ?Webhook $eventWebhook = null;
    protected ?string $ringbackTone = '';
    protected ?AdvancedMachineDetection $advancedMachineDetection = null;

    public function __construct(protected EndpointInterface $endpoint)
    {
    }

    public static function factory(EndpointInterface $endpoint): Connect
    {
        return new Connect($endpoint);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->toNCCOArray();
    }

    public function toNCCOArray(): array
    {
        $data = [
            'action' => 'connect',
            'endpoint' => [$this->endpoint->toArray()],
        ];

        if ($this->getTimeout()) {
            $data['timeout'] = $this->getTimeout();
        }

        if ($this->getLimit()) {
            $data['limit'] = $this->getLimit();
        }

        if ($this->getMachineDetection()) {
            $data['machineDetection'] = $this->getMachineDetection();
        }

        if ($this->getAdvancedMachineDetection()) {
            $data['advancedMachineDetection'] = $this->getAdvancedMachineDetection()->toArray();
        }

        $from = $this->getFrom();

        if ($from) {
            $data['from'] = $from;
        }

        $eventType = $this->getEventType();

        if ($eventType) {
            $data['eventType'] = $eventType;
        }

        $eventWebhook = $this->getEventWebhook();

        if ($eventWebhook) {
            $data['eventUrl'] = [$eventWebhook->getUrl()];
            $data['eventMethod'] = $eventWebhook->getMethod();
        }

        $ringbackTone = $this->getRingbackTone();

        if ($ringbackTone) {
            $data['ringbackTone'] = $ringbackTone;
        }

        return $data;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * @return $this
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): self
    {
        if ($eventType !== self::EVENT_TYPE_SYNCHRONOUS) {
            throw new InvalidArgumentException('Unknown event type for Connection action');
        }

        $this->eventType = $eventType;

        return $this;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getMachineDetection(): ?string
    {
        return $this->machineDetection;
    }

    public function setMachineDetection(string $machineDetection): self
    {
        if (
            $machineDetection !== self::MACHINE_CONTINUE &&
            $machineDetection !== self::MACHINE_HANGUP
        ) {
            throw new InvalidArgumentException('Unknown machine detection type');
        }

        $this->machineDetection = $machineDetection;

        return $this;
    }

    public function getEventWebhook(): ?Webhook
    {
        return $this->eventWebhook;
    }

    public function setEventWebhook(Webhook $eventWebhook): self
    {
        $this->eventWebhook = $eventWebhook;

        return $this;
    }

    public function getRingbackTone(): ?string
    {
        return $this->ringbackTone;
    }

    public function setRingbackTone(string $ringbackTone): self
    {
        $this->ringbackTone = $ringbackTone;

        return $this;
    }

    public function getAdvancedMachineDetection(): ?AdvancedMachineDetection
    {
        return $this->advancedMachineDetection;
    }

    public function setAdvancedMachineDetection(AdvancedMachineDetection $advancedMachineDetection): static
    {
        $this->advancedMachineDetection = $advancedMachineDetection;

        return $this;
    }
}
