<?php

declare(strict_types=1);

namespace Vonage\Conversation\Filter;

use Vonage\Entity\Filter\FilterInterface;

class ListUserConversationsFilter implements FilterInterface
{
    public const USER_STATE_INVITED = 'INVITED';
    public const USER_STATE_JOINED = 'JOINED';
    public const USER_STATE_LEFT = 'LEFT';
    public const ORDER_BY_CREATED = 'created';

    protected array $permittedUserStateValues = [
        self::USER_STATE_INVITED,
        self::USER_STATE_JOINED,
        self::USER_STATE_LEFT
    ];

    protected array $permittedOrderByValues = [
        self::ORDER_BY_CREATED
    ];

    protected ?string $state = null;
    protected ?string $startDate = null;
    protected ?string $endDate = null;
    protected ?string $orderBy = null;
    protected ?bool $includeCustomData = null;
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

    public function setStartDate(?string $startDate): ListUserConversationsFilter
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): ListUserConversationsFilter
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function setPageSize(?int $pageSize): ListUserConversationsFilter
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): ListUserConversationsFilter
    {
        if (!in_array($order, self::VALID_ORDERS)) {
            throw new \InvalidArgumentException($order . ' is an invalid order value');
        }

        $this->order = $order;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): ListUserConversationsFilter
    {
        if (! in_array($state, $this->permittedUserStateValues, true)) {
            throw new \InvalidArgumentException($state . ' is an invalid order value');
        }

        $this->state = $state;

        return $this;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): ListUserConversationsFilter
    {
        if (! in_array($orderBy, $this->permittedOrderByValues, true)) {
            throw new \InvalidArgumentException($orderBy . ' is an invalid orderBy value');
        }

        $this->orderBy = $orderBy;

        return $this;
    }

    public function getIncludeCustomData(): ?bool
    {
        return $this->includeCustomData;
    }

    public function setIncludeCustomData(?bool $includeCustomData): ListUserConversationsFilter
    {
        $this->includeCustomData = $includeCustomData;

        return $this;
    }

    public function getQuery(): array
    {
        $query = [];

        if ($this->getState()) {
            $query['state'] = $this->getState();
        }

        if ($this->getOrderBy()) {
            $query['order_by'] = $this->getOrderBy();
        }

        if (!is_null($this->getIncludeCustomData())) {
            $query['include_custom_data'] = $this->getIncludeCustomData();
        }

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
