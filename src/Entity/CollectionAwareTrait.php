<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
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

    /**
     * @param CollectionInterface $collection
     */
    public function setCollection(CollectionInterface $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * @return CollectionInterface
     */
    public function getCollection(): CollectionInterface
    {
        if (!isset($this->collection)) {
            throw new RuntimeException('missing collection');
        }

        return $this->collection;
    }
}
