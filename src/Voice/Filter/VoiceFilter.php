<?php
declare(strict_types=1);

namespace Vonage\Voice\Filter;

use DateTimeZone;
use Vonage\Entity\Filter\FilterInterface;

class VoiceFilter implements FilterInterface
{
    const STATUS_STARTED = 'started';
    const STATUS_RINGING = 'ringing';
    const STATUS_ANSWERED = 'answered';
    const STATUS_MACHINE = 'machine';
    const STATUS_COMPLETED = 'completed';
    const STATUS_BUSY = 'busy';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_TIMEOUT = 'timeout';
    const STATUS_UNANSWERED = 'unanswered';

    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var \DateTimeImmutable
     */
    protected $dateStart;

    /**
     * @var \DateTimeImmutable
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

    public function getQuery() : array
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

    public function getStatus() : ?string
    {
        return $this->status;
    }

    public function setStatus(string $status) : self
    {
        $this->status = $status;
        return $this;
    }

    public function getDateStart() : ?\DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeImmutable $dateStart) : self
    {
        $dateStart = $dateStart->setTimezone(new DateTimeZone('Z'));
        $this->dateStart = $dateStart;
        return $this;
    }

    public function getDateEnd() : ?\DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeImmutable $dateEnd) : self
    {
        $dateEnd = $dateEnd->setTimezone(new DateTimeZone('Z'));
        $this->dateEnd = $dateEnd;
        return $this;
    }

    public function getPageSize() : int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize) : self
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    public function getRecordIndex() : int
    {
        return $this->recordIndex;
    }

    public function setRecordIndex(int $recordIndex) : self
    {
        $this->recordIndex = $recordIndex;
        return $this;
    }

    public function getOrder() : string
    {
        return $this->order;
    }

    public function setOrder(string $order) : self
    {
        if ($order !== self::ORDER_ASC && $order !== self::ORDER_DESC) {
            throw new \InvalidArgumentException('Order must be `asc` or `desc`');
        }

        $this->order = $order;
        return $this;
    }

    public function getConversationUUID() : ?string
    {
        return $this->conversationUUID;
    }

    public function setConversationUUID(string $conversationUUID) : self
    {
        $this->conversationUUID = $conversationUUID;
        return $this;
    }
}
