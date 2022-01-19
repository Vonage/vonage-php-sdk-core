<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

use Countable;
use Iterator;
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Entity\Filter\EmptyFilter;
use Vonage\Entity\Filter\FilterInterface;

use function array_key_exists;
use function array_merge;
use function count;
use function filter_var;
use function http_build_query;
use function is_null;
use function json_decode;
use function md5;
use function strpos;

/**
 * Common code for iterating over a collection, and using the collection class to discover the API path.
 */
class IterableAPICollection implements ClientAwareInterface, Iterator, Countable
{
    use ClientAwareTrait;

    /**
     * @var APIResource
     */
    protected $api;

    /**
     * Determines if the collection will automatically go to the next page
     *
     * @var bool
     */
    protected $autoAdvance = true;

    /**
     * @var string
     */
    protected $baseUrl = Client::BASE_API;

    /**
     * Holds a cache of various pages we have already polled
     *
     * @var array<string, string>
     */
    protected $cache = [];

    /**
     * Index of the current resource of the current page
     *
     * @var int
     */
    protected $current;

    /**
     * Count the items in the response instead of returning the count parameter
     *
     * @deprected This exists for legacy reasons, will be removed in v3
     *
     * @var bool
     */
    protected $naiveCount = false;

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
    protected $index = 1;

    /**
     * @var bool
     */
    protected $isHAL = true;

    /**
     * User set pgge sixe.
     *
     * @var int
     */
    protected $size;

    /**
     * @var FilterInterface
     */
    protected $filter;

    /**
     * @var string
     */
    protected $collectionName = '';

    /**
     * @var string
     */
    protected $collectionPath;

    protected $hydrator;

    /**
     * @param $hydrator
     *
     * @return $this
     */
    public function setHydrator($hydrator): self
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @param $data
     * @param $id deprecated
     */
    public function hydrateEntity($data, $id = null)
    {
        if ($this->hydrator) {
            return $this->hydrator->hydrate($data);
        }

        return $data;
    }

    public function getResourceRoot(): array
    {
        // Handles issues where an API returns empty for no results, as opposed
        // to a proper API response with a count field of 0
        if (empty($this->page)) {
            return [];
        }

        $collectionName = $this->getApiResource()->getCollectionName();

        if ($this->getApiResource()->isHAL()) {
            return $this->page['_embedded'][$collectionName];
        }

        if (!empty($this->getApiResource()->getCollectionName())) {
            return $this->page[$collectionName];
        }

        return $this->page;
    }

    /**
     * Return the current item, expects concrete collection to handle creating the object.
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (is_null($this->current)) {
            $this->rewind();
        }

        return $this->hydrateEntity($this->getResourceRoot()[$this->current], $this->key());
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
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return
            $this->getResourceRoot()[$this->current]['id'] ??
            $this->getResourceRoot()[$this->current]['uuid'] ??
            $this->current;
    }

    /**
     * Handle pagination automatically (unless configured not to).
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function valid(): bool
    {
        //can't be valid if there's not a page (rewind sets this)
        if (!isset($this->page)) {
            return false;
        }

        //all hal collections have an `_embedded` object, we expect there to be a property matching the collection name
        if (
            $this->getApiResource()->isHAL() &&
            !isset($this->page['_embedded'][$this->getApiResource()->getCollectionName()])
        ) {
            return false;
        }

        //if we have a page with no items, we've gone beyond the end of the collection
        if (!count($this->getResourceRoot())) {
            return false;
        }

        // If there is no item at the current counter, we've gone beyond the end of this page
        if (!$this->getAutoAdvance() && !isset($this->getResourceRoot()[$this->current])) {
            return false;
        }

        //index the start of a page at 0
        if (is_null($this->current)) {
            $this->current = 0;
        }

        //if our current index is past the current page, fetch the next page if possible and reset the index
        if ($this->getAutoAdvance() && !isset($this->getResourceRoot()[$this->current])) {
            if (isset($this->page['_links'])) {
                if (isset($this->page['_links']['next'])) {
                    $this->fetchPage($this->page['_links']['next']['href']);
                    $this->current = 0;

                    return true;
                }
                // We don't have a next page, so we're done
                return false;
            }

            if ($this->current === count($this->getResourceRoot())) {
                $this->index++;
                $this->current = 0;
                $this->fetchPage($this->getApiResource()->getBaseUri());

                return !($this->count() === 0);
            }

            return false;
        }

        return true;
    }

    /**
     * Fetch the initial page
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function rewind(): void
    {
        $this->current = 0;
        $this->fetchPage($this->getApiResource()->getBaseUri());
    }

    /**
     * @return $this
     */
    public function setApiResource(APIResource $api): self
    {
        $this->api = $api;

        return $this;
    }

    public function getApiResource(): APIResource
    {
        return $this->api;
    }

    /**
     * Count of total items
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function count(): int
    {
        if (!isset($this->page)) {
            $this->rewind();
        }

        if (isset($this->page)) {
            // Force counting the items for legacy reasons
            if ($this->getNaiveCount()) {
                return count($this->getResourceRoot());
            }

            if (array_key_exists('total_items', $this->page)) {
                return $this->page['total_items'];
            }

            if (array_key_exists('count', $this->page)) {
                return $this->page['count'];
            }

            return count($this->getResourceRoot());
        }

        return 0;
    }

    /**
     * @return $this
     */
    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * @param $index
     *
     * @return $this
     */
    public function setPage($index): self
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
            if (array_key_exists('page', $this->page)) {
                return $this->page['page'];
            }

            return $this->page['page_index'];
        }

        if (isset($this->index)) {
            return $this->index;
        }

        throw new RuntimeException('page not set');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function getPageData(): ?array
    {
        if (is_null($this->page)) {
            $this->rewind();
        }

        return $this->page;
    }

    public function setPageData(array $data): void
    {
        $this->page = $data;
    }

    /**
     * @return int|mixed|void
     */
    public function getSize()
    {
        if (isset($this->page)) {
            if (array_key_exists('page_size', $this->page)) {
                return $this->page['page_size'];
            }

            return count($this->getResourceRoot());
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
    public function setSize($size): self
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
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
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

        $requestUri = $absoluteUri;

        if (filter_var($absoluteUri, FILTER_VALIDATE_URL) === false) {
            $requestUri = $this->getApiResource()->getBaseUrl() . $absoluteUri;
        }

        $cacheKey = md5($requestUri);
        if (array_key_exists($cacheKey, $this->cache)) {
            $this->page = $this->cache[$cacheKey];

            return;
        }

        $request = new Request($requestUri, 'GET');
        $response = $this->client->send($request);

        $this->getApiResource()->setLastRequest($request);
        $this->response = $response;
        $this->getApiResource()->setLastResponse($response);

        $body = $this->response->getBody()->getContents();
        $json = json_decode($body, true);
        $this->cache[md5($requestUri)] = $json;
        $this->page = $json;

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }
    }

    /**
     * @throws ClientException\Exception
     *
     * @return ClientException\Request|ClientException\Server
     */
    protected function getException(ResponseInterface $response)
    {
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);
        $status = (int)$response->getStatusCode();

        // Error responses aren't consistent. Some are generated within the
        // proxy and some are generated within voice itself. This handles
        // both cases

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = $body['error-code-label'] ?? $body['error_title'] ?? $body['title'] ?? 'Unexpected error';

        if ($status >= 400 && $status < 500) {
            $e = new ClientException\Request($errorTitle, $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new ClientException\Server($errorTitle, $status);
        } else {
            $e = new ClientException\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }

    public function getAutoAdvance(): bool
    {
        return $this->autoAdvance;
    }

    /**
     * @return $this
     */
    public function setAutoAdvance(bool $autoAdvance): self
    {
        $this->autoAdvance = $autoAdvance;

        return $this;
    }

    public function getNaiveCount(): bool
    {
        return $this->naiveCount;
    }

    /**
     * @return $this
     */
    public function setNaiveCount(bool $naiveCount): self
    {
        $this->naiveCount = $naiveCount;

        return $this;
    }
}
