<?php

declare(strict_types=1);

namespace Vonage\Users\Filter;

use InvalidArgumentException;
use Vonage\Entity\Filter\FilterInterface;

class UserFilter implements FilterInterface
{
    public const ORDER_ASC = 'asc';
    public const ORDER_DESC = 'desc';

    protected ?int $pageSize = null;

    protected ?string $order = null;
    protected ?string $cursor = null;

    public function getQuery(): array
    {
        $query = [];

        if ($this->pageSize !== null) {
            $query['page_size'] = $this->getPageSize();
        }

        if ($this->order !== null) {
            $query['order'] = $this->getOrder();
        }

        if ($this->cursor !== null) {
            $query['cursor'] = $this->getCursor();
        }

        return $query;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): static
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): static
    {
        if ($order !== self::ORDER_ASC && $order !== self::ORDER_DESC) {
            throw new InvalidArgumentException('Order must be `asc` or `desc`');
        }

        $this->order = $order;

        return $this;
    }

    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    public function setCursor(?string $cursor): static
    {
        $this->cursor = $cursor;

        return $this;
    }
}
