<?php

namespace Nexmo\Client;

use Nexmo\Entity\Collection;
use Nexmo\Entity\EmptyFilter;
use Nexmo\Entity\FilterInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;

class OpenAPIResource implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var Collection
     */
    protected $collectionPrototype;

    public function create(array $body)
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . $this->baseUri,
            'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->getClient()->send($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            throw $this->getException($response);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function delete(string $id) : void
    {
        $uri = $this->getClient()->getApiUrl() . $this->baseUri . '/' . $id;
        $request = new Request($uri, 'DELETE');

        $response = $this->getClient()->send($request);
    }

    public function get($id)
    {
        $uri = $this->getClient()->getApiUrl() . $this->baseUri . '/' . $id;
        $request = new Request($uri, 'GET', 'php://temp', ['accept' => 'application/json']);

        $response = $this->getClient()->send($request);
        $body = json_decode($response->getBody()->getContents(), true);

        return $body;
    }

    public function getBaseUri() : string
    {
        return $this->baseUri;
    }

    public function getCollectionName() : string
    {
        return $this->collectionName;
    }

    public function getCollectionPrototype() : Collection
    {
        if (is_null($this->collectionPrototype)) {
            $this->collectionPrototype = new Collection();
        }

        return clone $this->collectionPrototype;
    }

    protected function getException(ResponseInterface $response)
    {
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

        if (isset($body['description'])) {
            $errorTitle = $body['description'];
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

    public function search(FilterInterface $filter = null) : Collection
    {
        if (is_null($filter)) {
            $filter = new EmptyFilter();
        }

        $collection = $this->getCollectionPrototype();
        $collection
            ->setFilter($filter)
            ->setCollectionName($this->getCollectionName())
            ->setCollectionPath($this->getClient()->getApiUrl() . $this->baseUri)
        ;
        $collection->setClient($this->client);
        $collection->rewind();

        return $collection;
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

    public function setCollectionPrototype(Collection $prototype)
    {
        $this->collectionPrototype = $prototype;
        return $this;
    }

    public function update(string $id, array $body) : array
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . $this->baseUri . '/' . $id,
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->getClient()->send($request);

        if ($response->getStatusCode() != '200') {
            throw $this->getException($response);
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
