<?php

namespace Nexmo\Entity;

/**
 * A very simple key-value filter that can be used when less magic is needed
 */
class SimpleFilter implements FilterInterface
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
