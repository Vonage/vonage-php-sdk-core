<?php

declare(strict_types=1);

namespace Vonage\Voice\Webhook;

use DateTimeImmutable;
use Exception;

class Record
{
    /**
     * @var DateTimeImmutable
     */
    protected $startTime;

    /**
     * @var string
     */
    protected $recordingUrl;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $recordingUuid;

    /**
     * @var DateTimeImmutable
     */
    protected $endTime;

    /**
     * @var string
     */
    protected $conversationUuid;

    /**
     * @var DateTimeImmutable
     */
    protected $timestamp;

    /**
     * @throws Exception
     */
    public function __construct(array $event)
    {
        $this->startTime = new DateTimeImmutable($event['start_time']);
        $this->endTime = new DateTimeImmutable($event['end_time']);
        $this->timestamp = new DateTimeImmutable($event['timestamp']);
        $this->recordingUrl = $event['recording_url'];
        $this->recordingUuid = $event['recording_uuid'];
        $this->conversationUuid = $event['conversation_uuid'];
        $this->size = (int)$event['size'];
    }

    public function getStartTime(): DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getRecordingUrl(): string
    {
        return $this->recordingUrl;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getRecordingUuid(): string
    {
        return $this->recordingUuid;
    }

    public function getEndTime(): DateTimeImmutable
    {
        return $this->endTime;
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
