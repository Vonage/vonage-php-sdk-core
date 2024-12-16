<?php

declare(strict_types=1);

namespace Vonage\Client;

use Composer\InstalledVersions;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Uri;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LogLevel;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Entity\Filter\EmptyFilter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Entity\IterableAPICollection;
use Vonage\Entity\Filter\FilterInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\Credentials\Handler\HandlerInterface;
use Vonage\Logger\LoggerTrait;

use function is_null;
use function json_decode;
use function json_encode;
use function http_build_query;

class APIResource
{
    use LoggerTrait;

    /**
     * @var HandlerInterface[]
     */
    protected array $authHandlers = [];

    /**
     * Base URL that we will hit. This can be overridden from the underlying
     * client or directly on this class.
     */
    protected string $baseUrl = '';

    protected string $baseUri = '';

    protected string $collectionName = '';

    protected ?IterableAPICollection $collectionPrototype = null;

    /**
     * Sets flag that says to check for errors even on 200 Success
     */
    protected bool $errorsOn200 = false;

    /**
     * Error handler to use when reviewing API responses
     *
     * @var callable
     */
    protected $exceptionErrorHandler;

    protected bool $isHAL = true;

    protected ?RequestInterface $lastRequest = null;

    protected ?ResponseInterface $lastResponse = null;

    protected VonageConfig $vonageConfig;

    protected CredentialsInterface $credentials;

    public function __construct(VonageConfig $vonageConfig)
    {
        if (is_null($vonageConfig->getHttpClient())) {
            // Since the user did not pass a client, try and make a client
            // using the Guzzle 6 adapter or Guzzle 7 (depending on availability)
            [$guzzleVersion] = explode('@', (string) InstalledVersions::getVersion('guzzlehttp/guzzle'), 1);
            $guzzleVersion = (float) $guzzleVersion;

            if ($guzzleVersion >= 6.0 && $guzzleVersion < 7) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                /** @noinspection PhpUndefinedClassInspection */
                $client = new Client();
            }

            if ($guzzleVersion >= 7.0 && $guzzleVersion < 8.0) {
                $client = new GuzzleClient();
            }

            $vonageConfig->setHttpClient($client);
        }
    }

    public function getVonageConfig(): VonageConfig
    {
        return $this->vonageConfig;
    }

    public function setVonageConfig(VonageConfig $vonageConfig): APIResource
    {
        $this->vonageConfig = $vonageConfig;
        return $this;
    }

    public function getCredentials(): CredentialsInterface
    {
        return $this->credentials;
    }

    public function setCredentials(CredentialsInterface $credentials): APIResource
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * Adds authentication to a request
     *
     */
    public function addAuth(RequestInterface $request): RequestInterface
    {
        $credentials = $this->getCredentials();

        if (is_array($this->getAuthHandlers())) {
            foreach ($this->getAuthHandlers() as $handler) {
                try {
                    $request = $handler($request, $credentials);
                    break;
                } catch (\RuntimeException) {
                    continue; // We are OK if multiple are sent but only one match
                    // This has a really nasty side effect for complex handlers where we never see the error
                }
                throw new \RuntimeException(
                    'Unable to set credentials, please check configuration and 
                    supplied authentication'
                );
            }
            return $request;
        }

        return $this->getAuthHandlers()($request, $credentials);
    }

    /**
     * Wraps the HTTP Client, creates a new PSR-7 request adding authentication, signatures, etc.
     *
     * @throws ClientExceptionInterface
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        // Allow any part of the URI to be replaced with a simple search
        if (isset($this->options['url'])) {
            foreach ($this->options['url'] as $search => $replace) {
                $uri = (string)$request->getUri();
                $new = str_replace($search, $replace, $uri);

                if ($uri !== $new) {
                    $request = $request->withUri(new Uri($new));
                }
            }
        }

        // The user agent must be in the following format:
        // LIBRARY-NAME/LIBRARY-VERSION LANGUAGE-NAME/LANGUAGE-VERSION [APP-NAME/APP-VERSION]
        // See https://github.com/Vonage/client-library-specification/blob/master/SPECIFICATION.md#reporting
        $userAgent = [];

        // Library name
        $userAgent[] = 'vonage-php/' . $this->getVersion();

        // Language name
        $userAgent[] = 'php/' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        // If we have an app set, add that to the UA
        if (isset($this->options['app'])) {
            $app = $this->options['app'];
            $userAgent[] = $app['name'] . '/' . $app['version'];
        }

        // Set the header. Build by joining all the parts we have with a space
        $request = $request->withHeader('User-Agent', implode(' ', $userAgent));
        $response = $this->getVonageConfig()->getHttpClient()->sendRequest($request);

        if ($this->getVonageConfig()->isDebugMode()) {
            $id = uniqid('', true);
            $request->getBody()->rewind();
            $response->getBody()->rewind();

            $this->vonageConfig->getLogger()->log(
                LogLevel::DEBUG,
                'Request ' . $id,
                [
                    'url' => $request->getUri()->__toString(),
                    'headers' => $request->getHeaders(),
                    'body' => explode("\n", $request->getBody()->__toString())
                ]
            );

            $this->vonageConfig->getLogger()->log(
                LogLevel::DEBUG,
                'Response ' . $id,
                [
                    'headers ' => $response->getHeaders(),
                    'body' => explode("\n", $response->getBody()->__toString())
                ]
            );

            $request->getBody()->rewind();
            $response->getBody()->rewind();
        }

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function create(array $body, string $uri = '', array $headers = []): ?array
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

        if ($this->getAuthHandlers()) {
            $request = $this->addAuth($request);
        }

        $this->lastRequest = $request;

        $response = $this->getVonageConfig()->getHttpClient()->send($request);
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
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function delete(string $id, array $headers = []): ?array
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

        if ($this->getAuthHandlers()) {
            $request = $this->addAuth($request);
        }

        $response = $this->getVonageConfig()->getHttpClient()->send($request);
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
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function get(
        $id,
        array $query = [],
        array $headers = [],
        bool $jsonResponse = true,
        bool $uriOverride = false
    ) {
        $uri = $this->getBaseUrl() . $this->baseUri . '/' . $id;

        // This is a necessary hack if you want to fetch a totally different URL but use Vonage Auth
        if ($uriOverride) {
            $uri = $id;
        }

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

        if ($this->getAuthHandlers()) {
            $request = $this->addAuth($request);
        }

        $response = $this->getVonageConfig()->getHttpClient()->send($request);
        $status = (int)$response->getStatusCode();

        $this->lastRequest = $request;
        $this->setLastResponse($response);

        if ($status < 200 || $status > 299) {
            $e = $this->getException($response, $request);
            $e->setEntity($id);

            throw $e;
        }

        if (!$jsonResponse) {
            return $response->getBody();
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getAuthHandlers(): BasicHandler|array
    {
        // If we have not set a handler, default to Basic and issue warning.
        if (!$this->authHandlers) {
            $this->getVonageConfig()->getLogger()->log(
                LogLevel::WARNING,
                'Warning: no authorisation handler set for this Client. Defaulting to Basic which might not be
                the correct authorisation for this API call'
            );

            return new BasicHandler();
        }

        return $this->authHandlers;
    }

    public function getBaseUrl(): ?string
    {
        if ($this->getVonageConfig()->getBaseUrl()) {
            return $this->getVonageConfig()->getBaseUrl();
        }

        return $this->baseUrl;
    }

    public function getBaseUri(): ?string
    {
        return $this->baseUri;
    }

    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    public function getCollectionPrototype(): IterableAPICollection
    {
        if (is_null($this->collectionPrototype)) {
            $this->collectionPrototype = new IterableAPICollection();
        }

        return clone $this->collectionPrototype;
    }

    public function getExceptionErrorHandler(): callable
    {
        if (is_null($this->exceptionErrorHandler)) {
            return new APIExceptionHandler();
        }

        return $this->exceptionErrorHandler;
    }

    /**
     * Sets the error handler to use when reviewing API responses.
     */
    public function setExceptionErrorHandler(callable $handler): self
    {
        $this->exceptionErrorHandler = $handler;

        return $this;
    }

    protected function getException(ResponseInterface $response, RequestInterface $request)
    {
        return $this->getExceptionErrorHandler()($response, $request);
    }

    public function getLastRequest(): ?RequestInterface
    {
        $this->lastRequest->getBody()->rewind();

        return $this->lastRequest;
    }

    public function getLastResponse(): ?ResponseInterface
    {
        $this->lastResponse->getBody()->rewind();

        return $this->lastResponse;
    }

    public function isHAL(): bool
    {
        return $this->isHAL;
    }

    public function partiallyUpdate(string $id, array $body, array $headers = []): ?array
    {
        return $this->updateEntity('PATCH', $id, $body, $headers);
    }

    public function search(?FilterInterface $filter = null, string $uri = ''): IterableAPICollection
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

        return $collection;
    }

    /**
     * Set the auth handler(s). This can be a handler that extends off AbstractHandler,
     * or an array of handlers that will attempt to resolve at runtime
     *
     * @param HandlerInterface|array $handler
     *
     * @return $this
     */
    public function setAuthHandlers($handler): self
    {
        if (!is_array($handler)) {
            $handler = [$handler];
        }
        $this->authHandlers = $handler;

        return $this;
    }

    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = $url;

        return $this;
    }

    public function setBaseUri(string $uri): self
    {
        $this->baseUri = $uri;

        return $this;
    }

    public function setCollectionName(string $name): self
    {
        $this->collectionName = $name;

        return $this;
    }

    public function setCollectionPrototype(IterableAPICollection $prototype): self
    {
        $this->collectionPrototype = $prototype;

        return $this;
    }

    public function setIsHAL(bool $state): self
    {
        $this->isHAL = $state;

        return $this;
    }

    public function setLastResponse(ResponseInterface $response): self
    {
        $this->lastResponse = $response;

        return $this;
    }

    public function setLastRequest(RequestInterface $request): self
    {
        $this->lastRequest = $request;

        return $this;
    }

    /**
     * Allows form URL-encoded POST requests.
     *
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function submit(array $formData = [], string $uri = '', array $headers = []): string
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

        if ($this->getAuthHandlers()) {
            $request = $this->addAuth($request);
        }

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

    public function update(string $id, array $body, array $headers = []): ?array
    {
        return $this->updateEntity('PUT', $id, $body, $headers);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    protected function updateEntity(string $method, string $id, array $body, array $headers = []): ?array
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'application/json'];
        }

        $request = new Request(
            $this->getBaseUrl() . $this->baseUri . '/' . $id,
            $method,
            'php://temp',
            $headers
        );

        if ($this->getAuthHandlers()) {
            $request = $this->addAuth($request);
        }

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

    public function errorsOn200(): bool
    {
        return $this->errorsOn200;
    }

    public function setErrorsOn200(bool $value): self
    {
        $this->errorsOn200 = $value;

        return $this;
    }

    protected function getVersion(): string
    {
        return InstalledVersions::getVersion('vonage/client-core');
    }
}
