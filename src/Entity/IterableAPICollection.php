<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
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

    protected APIResource $api;

    /**
     * Determines if the collection will automatically go to the next page
     */
    protected bool $autoAdvance = true;

    protected string $baseUrl = Client::BASE_API;

    /**
     * Holds a cache of various pages we have already polled
     *
     * @var array<string, string>
     */
    protected array $cache = [];

    /**
     * Index of the current resource of the current page
     */
    protected int $current = 0;

    /**
     * Count the items in the response instead of returning the count parameter
     *
     * @deprected This exists for legacy reasons, will be removed in v3
     *
     * @var bool
     */
    protected bool $naiveCount = false;

    /**
     * Current page data.
     */
    protected ?array $pageData = null;

    /**
     * Last API Response
     */
    protected ?ResponseInterface $response = null;

    /**
     * User set page index.
     */
    protected int $index = 1;

    protected bool $isHAL = true;

    /**
     * User set pgge sixe.
     */
    protected ?int $size = null;

    protected ?FilterInterface $filter = null;

    protected string $collectionName = '';

    protected string $collectionPath = '';

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
        if (empty($this->pageData)) {
            return [];
        }

        $collectionName = $this->getApiResource()->getCollectionName();

        if ($this->getApiResource()->isHAL()) {
            return $this->pageData['_embedded'][$collectionName];
        }

        if (!empty($this->getApiResource()->getCollectionName())) {
            return $this->pageData[$collectionName];
        }

        return $this->pageData;
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
        if (!isset($this->pageData)) {
            return false;
        }

        //all hal collections have an `_embedded` object, we expect there to be a property matching the collection name
        if (
            $this->getApiResource()->isHAL() &&
            !isset($this->pageData['_embedded'][$this->getApiResource()->getCollectionName()])
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
            if (isset($this->pageData['_links'])) {
                if (isset($this->pageData['_links']['next'])) {
                    $this->fetchPage($this->pageData['_links']['next']['href']);
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
        if (!isset($this->pageData)) {
            $this->rewind();
        }

        if (isset($this->pageData)) {
            // Force counting the items for legacy reasons
            if ($this->getNaiveCount()) {
                return count($this->getResourceRoot());
            }

            if (array_key_exists('total_items', $this->pageData)) {
                return $this->pageData['total_items'];
            }

            if (array_key_exists('count', $this->pageData)) {
                return $this->pageData['count'];
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
        if (isset($this->pageData)) {
            if (array_key_exists('page', $this->pageData)) {
                return $this->pageData['page'];
            }

            return $this->pageData['page_index'];
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
        if (is_null($this->pageData)) {
            $this->rewind();
        }

        return $this->pageData;
    }

    public function setPageData(array $data): void
    {
        $this->pageData = $data;
    }

    /**
     * @return int|mixed|void
     */
    public function getSize()
    {
        if (isset($this->pageData)) {
            if (array_key_exists('page_size', $this->pageData)) {
                return $this->pageData['page_size'];
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
            $this->pageData = $this->cache[$cacheKey];

            return;
        }

        $request = new Request($requestUri, 'GET');

        if ($this->getApiResource()->getAuthHandler()) {
            $request = $this->getApiResource()->addAuth($request);
        }

        $response = $this->client->send($request);

        $this->getApiResource()->setLastRequest($request);
        $this->response = $response;
        $this->getApiResource()->setLastResponse($response);

        $body = $this->response->getBody()->getContents();
        $json = json_decode($body, true);
        $this->cache[md5($requestUri)] = $json;
        $this->pageData = $json;

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
