<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

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

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }
}
