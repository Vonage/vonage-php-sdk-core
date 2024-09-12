<?php

declare(strict_types=1);

namespace Vonage\Verify2\Filters;

use Vonage\Entity\Filter\FilterInterface;

class TemplateFilter implements FilterInterface
{
    protected ?int $pageSize = null;
    protected ?int $page = null;

    public function getQuery(): array
    {
        $return = [];

        if ($this->getPage()) {
            $return['page'] = $this->getPage();
        }

        if ($this->getPageSize()) {
            $return['page_size'] = $this->getPageSize();
        }

        return $return;
    }

    public function getPageSize(): ?int
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

    public function getPage(): ?int
    {
        return $this->pageSize;
    }

    /**
     * @return $this
     */
    public function setPage(int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }
}
