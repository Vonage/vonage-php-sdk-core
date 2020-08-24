<?php

namespace Vonage\Entity\Filter;

/**
 * A very simple key-value filter that can be used when less magic is needed
 */
class KeyValueFilter implements FilterInterface
{
    /**
     * @var array<string, string>
     */
    protected $query;

    /**
     * @param array<string, string> $query
     */
    public function __construct(array $query = [])
    {
        $this->query = $query;
    }

    public function getQuery() : array
    {
        return $this->query;
    }
}
