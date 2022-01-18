<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request;
use Vonage\Client\Exception\Server;

use function is_null;

/**
 * Convenience methods for iterable collections acting as services
 * This shim only exists to help with the transition away from old-style
 * CollectionsTrait implementations to service layers that return more
 * iterable collection objects
 *
 * @deprecated None of this should exist in the Service layer
 */
trait IterableServiceShimTrait
{
    /**
     * @var IterableAPICollection
     */
    protected $collection;

    /**
     * @param $data
     * @param $id
     *
     * @deprecated Use the hydrator directly
     */
    public function hydrateEntity($data, $id)
    {
        return $this->getHydrator()->hydrateObject($data, $id);
    }

    /**
     * Generates a collection object to help keep API compatibility
     *
     * @param $filter
     */
    protected function generateCollection($filter = null): void
    {
        $this->collection = $this->getApiResource()->search($filter);
        $this->collection->setHydrator($this->hydrator);
    }

    /**
     * Counts the current search query
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws Request
     * @throws Server
     *
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function count(): int
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->count();
    }

    /**
     * Returns the current object in the search
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws Request
     * @throws Server
     *
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->current();
    }

    /**
     * Returns the next object in the search
     *
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function next(): void
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        $this->collection->next();
    }

    /**
     * Returns the key of the current object in the search
     *
     * @return int|string
     *
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->key();
    }

    /**
     * Returns if the current iterable is valid
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws Request
     * @throws Server
     *
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function valid(): bool
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->valid();
    }

    /**
     * Rewinds the current iterable
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws Request
     * @throws Server
     *
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function rewind(): void
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        $this->collection->rewind();
    }

    /**
     * Sets the filter for a query
     *
     * @param $filter
     *
     * @return $this
     */
    public function setFilter($filter): self
    {
        $this->generateCollection($filter);

        return $this;
    }

    /**
     * Returns the current filter being used
     */
    public function getFilter(): Filter\FilterInterface
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->getFilter();
    }

    /**
     * Returns the current page the collection is looking at
     *
     * @return int|mixed
     */
    public function getPage()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->getPage();
    }

    /**
     * Sets the page that the collection should be on
     *
     * @param $index
     *
     * @return $this
     */
    public function setPage($index): self
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        $this->collection->setPage($index);

        return $this;
    }

    /**
     * Gets the size of the return result
     *
     * @return int|mixed|void
     */
    public function getSize()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->getSize();
    }

    /**
     * Sets the response number of embedded entities
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        $this->collection->setSize($size);

        return $this;
    }
}
