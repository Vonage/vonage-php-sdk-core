<?php

declare(strict_types=1);

namespace Vonage\Entity;

use Laminas\Diactoros\Request;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vonage\Entity\Filter\EmptyFilter;
use Vonage\Entity\Filter\FilterInterface;

use function array_merge;
use function count;
use function http_build_query;
use function is_null;
use function json_decode;
use function strpos;

/**
 * Common code for iterating over a collection, and using the collection class to discover the API path.
 */
trait CollectionTrait
{
    /**
     * Index of the current resource of the current page
     *
     * @var int
     */
    protected $current;

    /**
     * Current page data.
     *
     * @var array
     */
    protected $page;

    /**
     * Last API Response
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * User set page index.
     *
     * @var int
     */
    protected $index;

    /**
     * User set page size.
     *
     * @var int
     */
    protected $size;

    /**
     * @var FilterInterface
     */
    protected $filter;

    abstract public static function getCollectionName(): string;

    abstract public static function getCollectionPath(): string;

    /**
     * @param $data
     * @param $id
     */
    abstract public function hydrateEntity($data, $id);

    /**
     * Return the current item, expects concrete collection to handle creating the object.
     */
    public function current()
    {
        return $this->hydrateEntity($this->page['_embedded'][static::getCollectionName()][$this->current], $this->key());
    }

    /**
     * No checks here, just advance the index.
     */
    public function next(): void
    {
        $this->current++;
    }

    /**
     * Return the ID of the resource, in some cases this is `id`, in others `uuid`.
     *
     * @return string|int
     */
    public function key()
    {
        return
            $this->page['_embedded'][static::getCollectionName()][$this->current]['id'] ??
            $this->page['_embedded'][static::getCollectionName()][$this->current]['uuid'] ??
            $this->current;
    }

    /**
     * Handle pagination automatically (unless configured not to).
     */
    public function valid(): bool
    {
        //can't be valid if there's not a page (rewind sets this)
        if (!isset($this->page)) {
            return false;
        }

        //all hal collections have an `_embedded` object, we expect there to be a property matching the collection name
        if (!isset($this->page['_embedded'][static::getCollectionName()])) {
            return false;
        }

        //if we have a page with no items, we've gone beyond the end of the collection
        if (!count($this->page['_embedded'][static::getCollectionName()])) {
            return false;
        }

        //index the start of a page at 0
        if (is_null($this->current)) {
            $this->current = 0;
        }

        //if our current index is past the current page, fetch the next page if possible and reset the index
        if (!isset($this->page['_embedded'][static::getCollectionName()][$this->current])) {
            if (isset($this->page['_links']['next'])) {
                $this->fetchPage($this->page['_links']['next']['href']);
                $this->current = 0;

                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Fetch the initial page
     */
    public function rewind(): void
    {
        $this->fetchPage(static::getCollectionPath());
    }

    /**
     * Count of total items
     */
    public function count(): ?int
    {
        if (isset($this->page)) {
            return (int)$this->page['count'];
        }

        return null;
    }

    /**
     * @param $index
     *
     * @return $this
     */
    public function setPage($index): CollectionTrait
    {
        $this->index = (int)$index;

        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getPage()
    {
        if (isset($this->page)) {
            return $this->page['page_index'];
        }

        if (isset($this->index)) {
            return $this->index;
        }

        throw new RuntimeException('page not set');
    }

    /**
     * @return int|mixed
     */
    public function getSize()
    {
        if (isset($this->page)) {
            return $this->page['page_size'];
        }

        if (isset($this->size)) {
            return $this->size;
        }

        throw new RuntimeException('size not set');
    }

    /**
     * @param $size
     *
     * @return $this
     */
    public function setSize($size): CollectionTrait
    {
        $this->size = (int)$size;

        return $this;
    }

    /**
     * Filters reduce to query params and include paging settings.
     *
     * @return $this
     */
    public function setFilter(FilterInterface $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getFilter(): FilterInterface
    {
        if (!isset($this->filter)) {
            $this->setFilter(new EmptyFilter());
        }

        return $this->filter;
    }

    /**
     * Fetch a page using the current filter if no query is provided.
     *
     * @param $absoluteUri
     */
    protected function fetchPage($absoluteUri): void
    {
        //use filter if no query provided
        if (false === strpos($absoluteUri, '?')) {
            $query = [];

            if (isset($this->size)) {
                $query['page_size'] = $this->size;
            }

            if (isset($this->index)) {
                $query['page_index'] = $this->index;
            }

            if (isset($this->filter)) {
                $query = array_merge($this->filter->getQuery(), $query);
            }

            $absoluteUri .= '?' . http_build_query($query);
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . $absoluteUri,
            'GET'
        );

        $response = $this->client->send($request);

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $this->response = $response;
        $this->page = json_decode($this->response->getBody()->getContents(), true);
    }
}
