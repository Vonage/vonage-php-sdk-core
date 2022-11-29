<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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
