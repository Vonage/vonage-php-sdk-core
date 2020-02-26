<?php

namespace Nexmo\Client;

use Nexmo\Client;
use Zend\Diactoros\Request;
use Nexmo\Entity\EmptyFilter;
use Nexmo\Entity\FilterInterface;
use Nexmo\Entity\IterableAPICollection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class APIResource implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * Base URL that we will hit. This can be overriden from the underlying
     * client or directly on this class.
     * @var string
     */
    protected $baseUrl = Client::BASE_API;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var string
     */
    protected $collectionName = '';

    /**
     * @var Collection
     */
    protected $collectionPrototype;

    /**
     * @var bool
     */
    protected $isHAL = true;

    public function create(array $body)
    {
        $request = new Request(
            $this->baseUrl . $this->baseUri,
            'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->getClient()->send($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $e = $this->getException($response, $request);
            $e->setEntity($body);
            throw $e;
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function delete(string $id) : void
    {
        $uri = $this->getBaseUrl() . $this->baseUri . '/' . $id;
        $request = new Request($uri, 'DELETE');

        $response = $this->getClient()->send($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $e = $this->getException($response, $request);
            $e->setEntity($id);
            throw $e;
        }
    }

    public function get($id)
    {
        $uri = $this->getBaseUrl() . $this->baseUri . '/' . $id;
        $request = new Request($uri, 'GET', 'php://temp', ['accept' => 'application/json']);

        $response = $this->getClient()->send($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $e = $this->getException($response, $request);
            $e->setEntity($id);
            throw $e;
        }

        $body = json_decode($response->getBody()->getContents(), true);

        return $body;
    }

    public function getBaseUrl() : string
    {
        return $this->baseUrl;
    }

    public function getBaseUri() : string
    {
        return $this->baseUri;
    }

    public function getCollectionName() : string
    {
        return $this->collectionName;
    }

    public function getCollectionPrototype() : IterableAPICollection
    {
        if (is_null($this->collectionPrototype)) {
            $this->collectionPrototype = new IterableAPICollection();
        }

        return clone $this->collectionPrototype;
    }

    protected function getException(ResponseInterface $response, RequestInterface $request)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $response->getBody()->rewind();
        $status = $response->getStatusCode();

        // Error responses aren't consistent. Some are generated within the
        // proxy and some are generated within voice itself. This handles
        // both cases

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = 'Unexpected error';

        if (isset($body['title'])) {
            // Have to do this check to handle VAPI errors 
            if (is_string($body['type'])) {
                $errorTitle = sprintf(
                    "%s: %s. See %s for more information",
                    $body['title'],
                    $body['detail'],
                    $body['type']
                );
            } else {
                $errorTitle = $body['title'];
            }
        }

        if (isset($body['error_title'])) {
            $errorTitle = $body['error_title'];
        }

        if (isset($body['error-code-label'])) {
            $errorTitle = $body['error-code-label'];
        }

        if (isset($body['description'])) {
            $errorTitle = $body['description'];
        }

        if ($status >= 400 and $status < 500) {
            $e = new Exception\Request($errorTitle, $status);
            $e->setRequest($request);
            $e->setResponse($response);
        } elseif ($status >= 500 and $status < 600) {
            $e = new Exception\Server($errorTitle, $status);
            $e->setRequest($request);
            $e->setResponse($response);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }

    public function isHAL() : bool
    {
        return $this->isHAL;
    }

    public function search(FilterInterface $filter = null) : IterableAPICollection
    {
        if (is_null($filter)) {
            $filter = new EmptyFilter();
        }

        $collection = $this->getCollectionPrototype();
        $collection
            ->setApiResource($this)
            ->setFilter($filter)
        ;
        $collection->setClient($this->client);

        return $collection;
    }

    public function setBaseUrl(string $url) : self
    {
        $this->baseUrl = $url;
        return $this;
    }

    public function setBaseUri(string $uri) : self
    {
        $this->baseUri = $uri;
        return $this;
    }

    public function setCollectionName(string $name) : self
    {
        $this->collectionName = $name;
        return $this;
    }

    public function setCollectionPrototype(IterableAPICollection $prototype)
    {
        $this->collectionPrototype = $prototype;
        return $this;
    }

    public function setIsHAL(bool $state) : self
    {
        $this->isHAL = $state;
        return $this;
    }

    /**
     * Allows form URL-encoded POST requests
     */
    public function submit(array $formData = []) : string
    {
        $request = new Request(
            $this->baseUrl . $this->baseUri,
            'POST',
            'php://temp',
            ['content-type' => 'application/x-www-form-urlencoded']
        );

        $request->getBody()->write(http_build_query($formData));
        $response = $this->getClient()->send($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $e = $this->getException($response, $request);
            $e->setEntity($formData);
            throw $e;
        }

        return $response->getBody()->getContents();
    }

    public function update(string $id, array $body) : array
    {
        $request = new Request(
            $this->getBaseUrl() . $this->baseUri . '/' . $id,
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->getClient()->send($request);

        if ($response->getStatusCode() != '200') {
            $e = $this->getException($response, $request);
            $e->setEntity(['id' => $id, 'body' => $body]);
            throw $e;
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
