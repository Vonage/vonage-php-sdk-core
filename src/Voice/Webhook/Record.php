<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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
     * Record constructor.
     *
     * @param array $event
     *
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

    /**
     * @return DateTimeImmutable
     */
    public function getStartTime(): DateTimeImmutable
    {
        return $this->startTime;
    }

    /**
     * @return string
     */
    public function getRecordingUrl(): string
    {
        return $this->recordingUrl;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getRecordingUuid(): string
    {
        return $this->recordingUuid;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getEndTime(): DateTimeImmutable
    {
        return $this->endTime;
    }

    /**
     * @return string
     */
    public function getConversationUuid(): string
    {
        return $this->conversationUuid;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
