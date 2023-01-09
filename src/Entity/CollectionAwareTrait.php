<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

use RuntimeException;

trait CollectionAwareTrait
{
    /**
     * @var CollectionInterface
     */
    protected $collection;

    public function setCollection(CollectionInterface $collection): void
    {
        $this->collection = $collection;
    }

    public function getCollection(): CollectionInterface
    {
        if (!isset($this->collection)) {
            throw new RuntimeException('missing collection');
        }

        return $this->collection;
    }
}
