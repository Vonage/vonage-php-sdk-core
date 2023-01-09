<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Filter;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Vonage\Entity\Filter\FilterInterface;

class VoiceFilter implements FilterInterface
{
    public const STATUS_STARTED = 'started';
    public const STATUS_RINGING = 'ringing';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_MACHINE = 'machine';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_BUSY = 'busy';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_TIMEOUT = 'timeout';
    public const STATUS_UNANSWERED = 'unanswered';

    public const ORDER_ASC = 'asc';
    public const ORDER_DESC = 'desc';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var DateTimeImmutable
     */
    protected $dateStart;

    /**
     * @var DateTimeImmutable
     */
    protected $dateEnd;

    /**
     * @var int
     */
    protected $pageSize = 10;

    /**
     * @var int
     */
    protected $recordIndex = 0;

    /**
     * @var string
     */
    protected $order = 'asc';

    /**
     * @var string
     */
    protected $conversationUUID;

    public function getQuery(): array
    {
        $data = [
            'page_size' => $this->getPageSize(),
            'record_index' => $this->getRecordIndex(),
            'order' => $this->getOrder(),
        ];

        if ($this->getStatus()) {
            $data['status'] = $this->getStatus();
        }

        if ($this->getDateStart()) {
            $data['date_start'] = $this->getDateStart()->format('Y-m-d\TH:i:s\Z');
        }

        if ($this->getDateEnd()) {
            $data['date_end'] = $this->getDateEnd()->format('Y-m-d\TH:i:s\Z');
        }

        if ($this->getConversationUUID()) {
            $data['conversation_uuid'] = $this->getConversationUUID();
        }

        return $data;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateStart(): ?DateTimeImmutable
    {
        return $this->dateStart;
    }

    /**
     * @return $this
     */
    public function setDateStart(DateTimeImmutable $dateStart): self
    {
        $dateStart = $dateStart->setTimezone(new DateTimeZone('Z'));
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?DateTimeImmutable
    {
        return $this->dateEnd;
    }

    /**
     * @return $this
     */
    public function setDateEnd(DateTimeImmutable $dateEnd): self
    {
        $dateEnd = $dateEnd->setTimezone(new DateTimeZone('Z'));
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @return $this
     */
    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getRecordIndex(): int
    {
        return $this->recordIndex;
    }

    /**
     * @return $this
     */
    public function setRecordIndex(int $recordIndex): self
    {
        $this->recordIndex = $recordIndex;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @return $this
     */
    public function setOrder(string $order): self
    {
        if ($order !== self::ORDER_ASC && $order !== self::ORDER_DESC) {
            throw new InvalidArgumentException('Order must be `asc` or `desc`');
        }

        $this->order = $order;

        return $this;
    }

    public function getConversationUUID(): ?string
    {
        return $this->conversationUUID;
    }

    /**
     * @return $this
     */
    public function setConversationUUID(string $conversationUUID): self
    {
        $this->conversationUUID = $conversationUUID;

        return $this;
    }
}
