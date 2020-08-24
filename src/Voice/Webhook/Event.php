<?php
declare(strict_types=1);

namespace Vonage\Voice\Webhook;

class Event
{
    const STATUS_STARTED = 'started';
    const STATUS_RINGING = 'ringing';
    const STATUS_ANSWERED = 'answered';
    const STATUS_BUSY = 'busy';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_UNANSWERED = 'unanswered';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FAILED = 'failed';
    const STATUS_HUMAN = 'human';
    const STATUS_MACHINE = 'machine';
    const STATUS_TIMEOUT = 'timeout';
    const STATUS_COMPLETED = 'timeout';

    /**
     * @var string
     */
    protected $conversationUuid;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @var ?string
     */
    protected $duration;

    /**
     * @var ?\DateTimeImmutable
     */
    protected $endTime;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var ?string
     */
    protected $network;

    /**
     * @var ?string
     */
    protected $price;

    /**
     * @var ?string
     */
    protected $rate;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var ?\DateTimeImmutable
     */
    protected $startTime;

    /**
     * @var \DateTimeImmutable
     */
    protected $timestamp;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $uuid;

    public function __construct(array $event)
    {
        $this->from = $event['from'];
        $this->to = $event['to'];
        $this->uuid = $event['uuid'] ?? $event['call_uuid'];
        $this->conversationUuid = $event['conversation_uuid'];
        $this->status = $event['status'];
        $this->direction = $event['direction'];
        $this->timestamp = new \DateTimeImmutable($event['timestamp']);
        $this->rate = $event['rate'] ?? null;
        $this->network = $event['network'] ?? null;
        $this->duration = $event['duration'] ?? null;
        $this->price = $event['price'] ?? null;

        if (array_key_exists('start_time', $event) && !is_null($event['start_time'])) {
            $this->startTime = new \DateTimeImmutable($event['start_time']);
        }

        if (array_key_exists('end_time', $event)) {
            $this->endTime = new \DateTimeImmutable($event['end_time']);
        }
    }

    public function getConversationUuid() : string
    {
        return $this->conversationUuid;
    }

    public function getDirection() : string
    {
        return $this->direction;
    }

    public function getFrom() : string
    {
        return $this->from;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function getTimestamp() : \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getTo() : string
    {
        return $this->to;
    }

    public function getUuid() : string
    {
        return $this->uuid;
    }

    public function getNetwork() : ?string
    {
        return $this->network;
    }

    public function getRate() : ?string
    {
        return $this->rate;
    }

    public function getStartTime() : ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getEndTime() : ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function getDuration() : ?string
    {
        return $this->duration;
    }

    public function getPrice() : ?string
    {
        return $this->price;
    }
}
