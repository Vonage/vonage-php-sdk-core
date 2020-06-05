<?php
declare(strict_types=1);

namespace Nexmo\Voice\Filter;

use Nexmo\Entity\Filter\FilterInterface;

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
     * @var \DateTime
     */
    protected $dateStart;

    /**
     * @var \DateTime
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
            $data['date_start'] = $this->getDateStart()->format('Y-m-d H:i:s e');
        }

        if ($this->getDateEnd()) {
            $data['date_end'] = $this->getDateEnd()->format('Y-m-d H:i:s e');
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

    public function getDateStart() : ?\DateTime
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTime $dateStart) : self
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    public function getDateEnd() : ?\DateTime
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTime $dateEnd) : self
    {
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