<?php

declare(strict_types=1);

namespace Vonage\Conversation\Filter;

use Vonage\Entity\Filter\FilterInterface;

class ListMembersFilter implements FilterInterface
{
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

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function setPageSize(?int $pageSize): ListMembersFilter
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): ListMembersFilter
    {
        if (!in_array($order, self::VALID_ORDERS)) {
            throw new \InvalidArgumentException($order . ' is an invalid order value');
        }

        $this->order = $order;

        return $this;
    }

    public function setCursor(?string $cursor): ListMembersFilter
    {
        $this->cursor = $cursor;

        return $this;
    }

    public function getQuery(): array
    {
        $query = [];

        if ($this->getPageSize()) {
            $query['page_size'] = $this->getPageSize();
        }

        $query['order'] = $this->getOrder();

        return $query;
    }
}
