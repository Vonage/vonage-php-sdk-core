<?php

namespace Vonage\Entity;

use Vonage\Entity\IterableAPICollection;

/**
 * Convience methods for iteratable collections acting as services
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
    protected $collection = null;

    /**
     * @deprecated Use the hydrator directly
     */
    public function hydrateEntity($data, $id)
    {
        return $this->getHydrator()->hydrateObject($data, $id);
    }

        /**
     * Generates a collection object to help keep API compatibility
     */
    protected function generateCollection($filter = null)
    {
        $this->collection = $this->getApiResource()->search($filter);
        $this->collection->setHydrator($this->hydrator);
    }

    /**
     * Counts the current search query
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function count() : int
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->count();
    }

    /**
     * Returns the current object in the search
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function current()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->current();
    }

    /**
     * Returns the next object in the search
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function next()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->next();
    }

    /**
     * Returns the key of the current object in the search
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function key()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->key();
    }

    /**
     * Returns if the current iterable is valid
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function valid()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->valid();
    }

    /**
     * Rewinds the current iterable
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function rewind()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->rewind();
    }

    /**
     * Sets the filter for a query
     */
    public function setFilter($filter)
    {
        $this->generateCollection($filter);
        return $this;
    }

    /**
     * Returns the current filter being used
     */
    public function getFilter()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->getFilter();
    }

    /**
     * Returns the current page the collection is looking at
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
     */
    public function setPage($index)
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        $this->collection->setPage($index);
        return $this;
    }

    /**
     * Gets the size of the return result
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
     */
    public function setSize(int $size)
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        $this->collection->setSize($size);
        return $this;
    }
}
