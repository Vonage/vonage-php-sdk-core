<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

use RuntimeException;

/**
 * Common code for iterating over a collection, and using the collection class to discover the API path.
 */
trait ModernCollectionTrait
{
    use CollectionTrait;

    /**
     * Count of total items
     */
    public function count(): int
    {
        if (isset($this->page)) {
            return (int)$this->page['total_items'];
        }

        return 0;
    }

    /**
     * @return int|mixed
     */
    public function getPage()
    {
        if (isset($this->page)) {
            return $this->page['page'];
        }

        if (isset($this->index)) {
            return $this->index;
        }

        throw new RuntimeException('page not set');
    }
}
