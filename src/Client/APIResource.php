<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Client;

use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Entity\Filter\EmptyFilter;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\IterableAPICollection;

class APIResource implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * Base URL that we will hit. This can be overridden from the underlying
     * client or directly on this class.
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var string
     */
    protected $collectionName = '';

    /**
     * @var IterableAPICollection
     */
    protected $collectionPrototype;

    /**
     * Sets flag that says to check for errors even on 200 Success
     * @var bool
     */
    protected $errorsOn200 = false;

    /**
     * Error handler to use when reviewing API responses
     * @var callable
     */
    protected $exceptionErrorHandler;

    /**
     * @var bool
     */
    protected $isHAL = true;

    /**
     * @var RequestInterface
     */
    protected $lastRequest;

    /**
     * @var ResponseInterface
     */
    protected $lastResponse;

    /**
     * @param array $body
     * @param string $uri
     * @param array $headers
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function create(array $body, string $uri = '', $headers = []): ?array
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'application/json'];
        }

        $request = new Request(
            $this->getBaseUrl() . $this->getBaseUri() . $uri,
            'POST',
            'php://temp',
            $headers
        );

        $request->getBody()->write(json_encode($body));
        $this->lastRequest = $request;

        $response = $this->getClient()->send($request);
        $status = (int)$response->getStatusCode();

        $this->setLastResponse($response);

        if (($status < 200 || $status > 299) || $this->errorsOn200()) {
            $e = $this->getException($response, $request);

            if ($e) {
                $e->setEntity($body);

                throw $e;
            }
        }

        $response->getBody()->rewind();

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $id
     * @param array $headers
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function delete(string $id, $headers = []): ?array
    {
        $uri = $this->getBaseUrl() . $this->baseUri . '/' . $id;

        if (empty($headers)) {
            $headers = [
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ];
        }

        $request = new Request(
            $uri,
            'DELETE',
            'php://temp',
            $headers
        );

        $response = $this->getClient()->send($request);
        $status = (int)$response->getStatusCode();

        $this->lastRequest = $request;
        $this->setLastResponse($response);

        if ($status < 200 || $status > 299) {
            $e = $this->getException($response, $request);
            $e->setEntity($id);

            throw $e;
        }

        $response->getBody()->rewind();

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $id
     * @param array $query
     * @param array $headers
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function get($id, array $query = [], $headers = [])
    {
        $uri = $this->getBaseUrl() . $this->baseUri . '/' . $id;

        if (!empty($query)) {
            $uri .= '?' . http_build_query($query);
        }

        if (empty($headers)) {
            $headers = [
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ];
        }

        $request = new Request(
            $uri,
            'GET',
            'php://temp',
            $headers
        );

        $response = $this->getClient()->send($request);
        $status = (int)$response->getStatusCode();

        $this->lastRequest = $request;
        $this->setLastResponse($response);

        if ($status < 200 || $status > 299) {
            $e = $this->getException($response, $request);
            $e->setEntity($id);

            throw $e;
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        if (!$this->baseUrl && $this->client) {
            $this->baseUrl = $this->client->getApiUrl();
        }

        return $this->baseUrl;
    }

    /**
     * @return string|null
     */
    public function getBaseUri(): ?string
    {
        return $this->baseUri;
    }

    /**
     * @return string
     */
    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    /**
     * @return IterableAPICollection
     */
    public function getCollectionPrototype(): IterableAPICollection
    {
        if (is_null($this->collectionPrototype)) {
            $this->collectionPrototype = new IterableAPICollection();
        }

        return clone $this->collectionPrototype;
    }

    /**
     * @return callable
     */
    public function getExceptionErrorHandler(): callable
    {
        if (is_null($this->exceptionErrorHandler)) {
            return new APIExceptionHandler();
        }

        return $this->exceptionErrorHandler;
    }

    /**
     * Sets the error handler to use when reviewing API responses.
     *
     * @param callable $handler
     * @return $this
     */
    public function setExceptionErrorHandler(callable $handler): self
    {
        $this->exceptionErrorHandler = $handler;

        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @return mixed
     */
    protected function getException(ResponseInterface $response, RequestInterface $request)
    {
        return $this->getExceptionErrorHandler()($response, $request);
    }

    /**
     * @return RequestInterface|null
     */
    public function getLastRequest(): ?RequestInterface
    {
        $this->lastRequest->getBody()->rewind();

        return $this->lastRequest;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getLastResponse(): ?ResponseInterface
    {
        $this->lastResponse->getBody()->rewind();

        return $this->lastResponse;
    }

    /**
     * @return bool
     */
    public function isHAL(): bool
    {
        return $this->isHAL;
    }

    /**
     * @param FilterInterface|null $filter
     * @param string $uri
     * @return IterableAPICollection
     */
    public function search(FilterInterface $filter = null, string $uri = ''): IterableAPICollection
    {
        if (is_null($filter)) {
            $filter = new EmptyFilter();
        }

        $api = clone $this;

        if ($uri) {
            $api->setBaseUri($uri);
        }

        $collection = $this->getCollectionPrototype();
        $collection
            ->setApiResource($api)
            ->setFilter($filter);
        $collection->setClient($this->client);

        return $collection;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setBaseUri(string $uri): self
    {
        $this->baseUri = $uri;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setCollectionName(string $name): self
    {
        $this->collectionName = $name;

        return $this;
    }

    /**
     * @param IterableAPICollection $prototype
     * @return $this
     */
    public function setCollectionPrototype(IterableAPICollection $prototype): self
    {
        $this->collectionPrototype = $prototype;

        return $this;
    }

    /**
     * @param bool $state
     * @return $this
     */
    public function setIsHAL(bool $state): self
    {
        $this->isHAL = $state;

        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setLastResponse(ResponseInterface $response): self
    {
        $this->lastResponse = $response;

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function setLastRequest(RequestInterface $request): self
    {
        $this->lastRequest = $request;

        return $this;
    }

    /**
     * Allows form URL-encoded POST requests.
     *
     * @param array $formData
     * @param string $uri
     * @param array $headers
     * @return string
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function submit(array $formData = [], string $uri = '', $headers = []): string
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'application/x-www-form-urlencoded'];
        }

        $request = new Request(
            $this->baseUrl . $this->baseUri . $uri,
            'POST',
            'php://temp',
            $headers
        );

        $request->getBody()->write(http_build_query($formData));
        $response = $this->getClient()->send($request);
        $status = $response->getStatusCode();

        $this->lastRequest = $request;
        $this->setLastResponse($response);

        if ($status < 200 || $status > 299) {
            $e = $this->getException($response, $request);
            $e->setEntity($formData);

            throw $e;
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param string $id
     * @param array $body
     * @param array $headers
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function update(string $id, array $body, $headers = []): ?array
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'application/json'];
        }

        $request = new Request(
            $this->getBaseUrl() . $this->baseUri . '/' . $id,
            'PUT',
            'php://temp',
            $headers
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->getClient()->send($request);

        $this->lastRequest = $request;
        $this->setLastResponse($response);
        $status = $response->getStatusCode();

        if (($status < 200 || $status > 299) || $this->errorsOn200()) {
            $e = $this->getException($response, $request);
            $e->setEntity(['id' => $id, 'body' => $body]);

            throw $e;
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return bool
     */
    public function errorsOn200(): bool
    {
        return $this->errorsOn200;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setErrorsOn200(bool $value): self
    {
        $this->errorsOn200 = $value;
        return $this;
    }
}
