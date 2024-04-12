<?php

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
