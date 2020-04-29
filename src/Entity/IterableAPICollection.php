<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Entity;

use Countable;
use \Iterator;
use Nexmo\Client;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\Exception;
use Nexmo\Client\APIResource;

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
     * @var string
     */
    protected $baseUrl = Client::BASE_API;

    /**
     * Holds a cache of various pages we have already polled
     * @var array<string, string>
     */
    protected $cache = [];

    /**
     * Index of the current resource of the current page
     * @var int
     */
    protected $current;

    /**
     * Current page data.
     * @var array
     */
    protected $page;

    /**
     * Last API Response
     * @var ResponseInterface
     */
    protected $response;

    /**
     * User set page index.
     * @var int
     */
    protected $index;

    /**
     * @var bool
     */
    protected $isHAL = true;

    /**
     * User set pgge sixe.
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

    protected $prototype;

    public function setHydrator($hydrator) : self
    {
        $this->hydrator = $hydrator;
        return $this;
    }
    
    public function hydrateEntity($data, $id = null)
    {
        if ($this->hydrator) {
            $object = $this->hydrator->hydrate($data);
            return $object;
        }

        return $data;
    }

    public function getResourceRoot() : array
    {
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
     * @return mixed
     */
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
    public function next()
    {
        $this->current++;
    }

    /**
     * Return the ID of the resource, in some cases this is `id`, in others `uuid`.
     * @return string
     */
    public function key()
    {
        if (isset($this->getResourceRoot()[$this->current]['id'])) {
            return $this->getResourceRoot()[$this->current]['id'];
        } elseif (isset($this->getResourceRoot()[$this->current]['uuid'])) {
            return $this->getResourceRoot()[$this->current]['uuid'];
        }

        return $this->current;
    }

    /**
     * Handle pagination automatically (unless configured not to).
     * @return bool
     */
    public function valid()
    {
        //can't be valid if there's not a page (rewind sets this)
        if (!isset($this->page)) {
            return false;
        }

        //all hal collections have an `_embedded` object, we expect there to be a property matching the collection name
        if ($this->getApiResource()->isHAL()) {
            if (!isset($this->page['_embedded'])
                || !isset($this->page['_embedded'][$this->getApiResource()->getCollectionName()])
            ) {
                return false;
            }
        }

        //if we have a page with no items, we've gone beyond the end of the collection
        if (!count($this->getResourceRoot())) {
            return false;
        }

        //index the start of a page at 0
        if (is_null($this->current)) {
            $this->current = 0;
        }

        //if our current index is past the current page, fetch the next page if possible and reset the index
        if (!isset($this->getResourceRoot()[$this->current])) {
            if (isset($this->page['_links']) and isset($this->page['_links']['next'])) {
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
    public function rewind()
    {
        $this->current = 0;
        $this->fetchPage($this->getApiResource()->getBaseUri());
    }

    public function setApiResource(APIResource $api)
    {
        $this->api = $api;
        return $this;
    }

    public function getApiResource() : APIResource
    {
        return $this->api;
    }

    /**
     * Count of total items
     * @return integer
     */
    public function count()
    {
        if (!isset($this->page)) {
            $this->rewind();
        }

        if (isset($this->page)) {
            if (array_key_exists('total_items', $this->page)) {
                return $this->page['total_items'];
            }

            return count($this->getResourceRoot());
        }
    }

    public function setBaseUrl(string $url) : self
    {
        $this->baseUrl = $url;
        return $this;
    }

    public function setPage($index)
    {
        $this->index = (int) $index;
        return $this;
    }

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

        throw new \RuntimeException('page not set');
    }

    public function getPageData() : ?array
    {
        if (is_null($this->page)) {
            $this->rewind();
        }

        return $this->page;
    }

    public function getSize()
    {
        if (isset($this->page)) {
            return $this->page['page_size'];
        }

        if (isset($this->size)) {
            return $this->size;
        }

        throw new \RuntimeException('size not set');
    }

    public function setSize($size)
    {
        $this->size = (int) $size;
        return $this;
    }

    /**
     * Filters reduce to query params and include paging settings.
     *
     * @param FilterInterface $filter
     * @return $this
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter()
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
    protected function fetchPage($absoluteUri)
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

        $this->response = $response;
        $this->getApiResource()->setLastResponse($response);

        $body = $this->response->getBody()->getContents();
        $json = json_decode($body, true);
        $this->cache[md5($requestUri)] = $json;
        $this->page = $json;

        if ($response->getStatusCode() != '200') {
            $e = $this->getException($response);
            throw $e;
        }
    }

    protected function getException(ResponseInterface $response)
    {
        $response->getBody()->rewind();
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        // Error responses aren't consistent. Some are generated within the
        // proxy and some are generated within voice itself. This handles
        // both cases

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = 'Unexpected error';

        if (isset($body['title'])) {
            $errorTitle = $body['title'];
        }

        if (isset($body['error_title'])) {
            $errorTitle = $body['error_title'];
        }

        if (isset($body['error-code-label'])) {
            $errorTitle = $body['error-code-label'];
        }

        if ($status >= 400 and $status < 500) {
            $e = new Exception\Request($errorTitle, $status);
        } elseif ($status >= 500 and $status < 600) {
            $e = new Exception\Server($errorTitle, $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }
}
