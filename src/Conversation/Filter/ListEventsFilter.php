<?php

declare(strict_types=1);

namespace Vonage\Conversation\Filter;

use Vonage\Entity\Filter\FilterInterface;

class ListEventsFilter implements FilterInterface
{
    protected ?int $startId = null;
    protected ?int $endId = null;
    protected ?string $eventType = null;
    protected ?bool $excludeDeletedEvents = null;
    protected int $pageSize = 10;
    protected string $order = 'asc';
    private const VALID_ORDERS = ['asc', 'ASC', 'desc', 'DESC'];

    /**
     * @var string|null
     * Private as you should only follow cursors provided, not set them
     */
    private ?string $cursor = null;

    public function __construct()
    {
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(?string $eventType): ListEventsFilter
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getExcludeDeletedEvents(): ?bool
    {
        return $this->excludeDeletedEvents;
    }

    public function setExcludeDeletedEvents(?bool $excludeDeletedEvents): ListEventsFilter
    {
        $this->excludeDeletedEvents = $excludeDeletedEvents;

        return $this;
    }

    public function getStartId(): ?int
    {
        return $this->startId;
    }

    public function setStartId(?int $startId): ListEventsFilter
    {
        $this->startId = $startId;

        return $this;
    }

    public function getEndId(): ?int
    {
        return $this->endId;
    }

    public function setEndId(?int $endId): ListEventsFilter
    {
        $this->endId = $endId;

        return $this;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function setPageSize(?int $pageSize): ListEventsFilter
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): ListEventsFilter
    {
        if (!in_array($order, self::VALID_ORDERS)) {
            throw new \InvalidArgumentException($order . ' is an invalid order value');
        }

        $this->order = $order;

        return $this;
    }

    public function setCursor(?string $cursor): ListEventsFilter
    {
        $this->cursor = $cursor;

        return $this;
    }

    public function getQuery(): array
    {
        $query = [];

        if ($this->getStartId()) {
            $query['start_id'] = $this->getStartId();
        }

        if ($this->getEndId()) {
            $query['end_id'] = $this->getEndId();
        }

        if ($this->getEventType()) {
            $query['event_type'] = $this->getEventType();
        }

        if ($this->getExcludeDeletedEvents()) {
            $query['exclude_deleted_events'] = $this->getExcludeDeletedEvents();
        }

        $query['page_size'] = $this->getPageSize();
        $query['order'] = $this->getOrder();

        return $query;
    }
}
