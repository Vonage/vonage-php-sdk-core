<?php
declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use Vonage\Voice\Webhook;
use Vonage\Voice\Endpoint\EndpointInterface;

class Connect implements ActionInterface
{
    const EVENT_TYPE_SYNCHRONOUS = 'syncchronous';

    const MACHINE_CONTINUE = 'continue';
    const MACHINE_HANGUP = 'hangup';

    /**
     * @var EndpointInterface
     */
    protected $endpoint;

    /**
     * @var ?string
     */
    protected $from;

    /**
     * @var ?string
     */
    protected $eventType;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var string
     */
    protected $machineDetection;

    /**
     * @var ?Webhook
     */
    protected $eventWebhook;

    /**
     * @var ?string
     */
    protected $ringbackTone;

    public function __construct(EndpointInterface $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public static function factory(EndpointInterface $endpoint, array $data = []) : Connect
    {
        $connect = new Connect($endpoint);

        return $connect;
    }

    public function jsonSerialize()
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

    public function getFrom() : ?string
    {
        return $this->from;
    }

    public function setFrom(string $from) : self
    {
        $this->from = $from;
        return $this;
    }

    public function getEventType() : ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType) : self
    {
        if ($eventType !== self::EVENT_TYPE_SYNCHRONOUS) {
            throw new \InvalidArgumentException('Unknown event type for Connection action');
        }

        $this->eventType = $eventType;
        return $this;
    }

    public function getTimeout() : ?int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout) : self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function getLimit() : ?int
    {
        return $this->limit;
    }

    public function setLimit(int $limit) : self
    {
        $this->limit = $limit;
        return $this;
    }

    public function getMachineDetection() : ?string
    {
        return $this->machineDetection;
    }

    public function setMachineDetection(string $machineDetection) : self
    {
        if ($machineDetection !== self::MACHINE_CONTINUE &&
            $machineDetection !== self::MACHINE_HANGUP
        ) {
            throw new \InvalidArgumentException('Uknown machine detection type');
        }

        $this->machineDetection = $machineDetection;
        return $this;
    }

    public function getEventWebhook() : ?Webhook
    {
        return $this->eventWebhook;
    }

    public function setEventWebhook(Webhook $eventWebhook) : self
    {
        $this->eventWebhook = $eventWebhook;
        return $this;
    }

    public function getRingbackTone() : ?string
    {
        return $this->ringbackTone;
    }

    public function setRingbackTone(string $ringbackTone) : self
    {
        $this->ringbackTone = $ringbackTone;
        return $this;
    }
}
