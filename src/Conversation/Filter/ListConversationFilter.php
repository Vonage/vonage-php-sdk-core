<?php

declare(strict_types=1);

namespace Vonage\Conversation\Filter;

use Vonage\Entity\Filter\FilterInterface;

class ListConversationFilter implements FilterInterface
{
    protected ?string $startDate = null;
    protected ?string $endDate = null;
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

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): ListConversationFilter
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): ListConversationFilter
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function setPageSize(?int $pageSize): ListConversationFilter
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): ListConversationFilter
    {
        if (!in_array($order, self::VALID_ORDERS)) {
            throw new \InvalidArgumentException($order . ' is an invalid order value');
        }

        $this->order = $order;

        return $this;
    }

    public function setCursor(?string $cursor): ListConversationFilter
    {
        $this->cursor = $cursor;

        return $this;
    }

    public function getQuery(): array
    {
        $query = [];

        if ($this->getStartDate()) {
            $query['date_start'] = $this->getStartDate();
        }

        if ($this->getEndDate()) {
            $query['date_end'] = $this->getEndDate();
        }

        if ($this->getPageSize()) {
            $query['page_size'] = $this->getPageSize();
        }

        $query['order'] = $this->getOrder();

        return $query;
    }
}
