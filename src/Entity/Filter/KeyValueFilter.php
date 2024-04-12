<?php

declare(strict_types=1);

namespace Vonage\Entity\Filter;

/**
 * A very simple key-value filter that can be used when less magic is needed
 */
class KeyValueFilter implements FilterInterface
{
    /**
     * @param array<string, string> $query
     */
    public function __construct(protected array $query = [])
    {
    }

    public function getQuery(): array
    {
        return $this->query;
    }
}
