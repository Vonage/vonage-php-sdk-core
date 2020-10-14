<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage;

use Http\Client\HttpClient;
use InvalidArgumentException;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Uri;
use Lcobucci\JWT\Token;
use PackageVersions\Versions;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vonage\Account\ClientFactory;
use Vonage\Call\Collection;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\OAuth;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Factory\FactoryInterface;
use Vonage\Client\Factory\MapFactory;
use Vonage\Client\Signature;
use Vonage\Entity\EntityInterface;
use Vonage\Verify\Verification;

/**
 * Vonage API Client, allows access to the API from PHP.
 *
 * @method Account\Client account()
 * @method Message\Client message()
 * @method Application\Client applications()
 * @method Conversion\Client conversion()
 * @method Insights\Client insights()
 * @method Numbers\Client numbers()
 * @method Redact\Client redact()
 * @method SMS\Client sms()
 * @method Verify\Client  verify()
 * @method Voice\Client voice()
 *
 * @property string restUrl
 * @property string apiUrl
 */
class Client
{
    public const VERSION = '2.4.2';
    public const BASE_API = 'https://api.nexmo.com';
    public const BASE_REST = 'https://rest.nexmo.com';

    /**
     * API Credentials
     * @var CredentialsInterface
     */
    protected $credentials;

    /**
     * Http Client
     * @var HttpClient
     */
    protected $client;

    /**
     * @var ContainerInterface
     */
    protected $factory;

    /**
     * @var array
     */
    protected $options = ['show_deprecations' => false];

    /**
     * Create a new API client using the provided credentials.
     *
     * @param CredentialsInterface $credentials
     * @param array $options
     * @param ClientInterface|null $client
     */
    public function __construct(CredentialsInterface $credentials, $options = [], ClientInterface $client = null)
    {
        if (is_null($client)) {
            // Since the user did not pass a client, try and make a client
            $client = new \GuzzleHttp\Client();
        }

        $this->setHttpClient($client);

        //make sure we know how to use the credentials
        if (!($credentials instanceof Container) &&
            !($credentials instanceof Basic) &&
            !($credentials instanceof SignatureSecret) &&
            !($credentials instanceof OAuth) &&
            !($credentials instanceof Keypair)
        ) {
            throw new RuntimeException('unknown credentials type: ' . get_class($credentials));
        }

        $this->credentials = $credentials;

        $this->options = array_merge($this->options, $options);

        // If they've provided an app name, validate it
        if (isset($options['app'])) {
            $this->validateAppOptions($options['app']);
        }

        // Set the default URLs. Keep the constants for
        // backwards compatibility
        $this->apiUrl = static::BASE_API;
        $this->restUrl = static::BASE_REST;

        // If they've provided alternative URLs, use that instead
        // of the defaults
        if (isset($options['base_rest_url'])) {
            $this->restUrl = $options['base_rest_url'];
        }

        if (isset($options['base_api_url'])) {
            $this->apiUrl = $options['base_api_url'];
        }

        $this->setFactory(new MapFactory([
            // Legacy Namespaces
            'message' => Message\Client::class,
            'calls' => Collection::class,
            'conversation' => Conversations\Collection::class,
            'user' => User\Collection::class,

            // Registered Services by name
            'account' => ClientFactory::class,
            'applications' => Application\ClientFactory::class,
            'conversion' => Conversion\ClientFactory::class,
            'insights' => Insights\ClientFactory::class,
            'numbers' => Numbers\ClientFactory::class,
            'redact' => Redact\ClientFactory::class,
            'sms' => SMS\ClientFactory::class,
            'verify' => Verify\ClientFactory::class,
            'voice' => Voice\ClientFactory::class,

            // Additional utility classes
            APIResource::class => APIResource::class,
        ], $this));

        // Disable throwing E_USER_DEPRECATED notices by default, the user can turn it on during development
        if (array_key_exists('show_deprecations', $this->options) && !$this->options['show_deprecations']) {
            set_error_handler(
                static function () {
                    return true;
                },
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * @return string
     */
    public function getRestUrl(): string
    {
        return $this->restUrl;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Set the Http Client to used to make API requests.
     *
     * This allows the default http client to be swapped out for a HTTPlug compatible
     * replacement.
     *
     * @param ClientInterface $client
     * @return $this
     */
    public function setHttpClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the Http Client used to make API requests.
     *
     * @return \GuzzleHttp\Client|HttpClient
     */
    public function getHttpClient()
    {
        return $this->client;
    }

    /**
     * Set the factory used to create API specific clients.
     *
     * @param FactoryInterface $factory
     * @return $this
     */
    public function setFactory(FactoryInterface $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getFactory(): ContainerInterface
    {
        return $this->factory;
    }

    /**
     * @param RequestInterface $request
     * @param SignatureSecret $credentials
     * @return RequestInterface
     * @throws Exception
     */
    public static function signRequest(RequestInterface $request, SignatureSecret $credentials): RequestInterface
    {
        switch ($request->getHeaderLine('content-type')) {
            case 'application/json':
                $body = $request->getBody();
                $body->rewind();
                $content = $body->getContents();
                $params = json_decode($content, true);
                $params['api_key'] = $credentials['api_key'];
                $signature = new Signature($params, $credentials['signature_secret'], $credentials['signature_method']);
                $body->rewind();
                $body->write(json_encode($signature->getSignedParams()));
                break;
            case 'application/x-www-form-urlencoded':
                $body = $request->getBody();
                $body->rewind();
                $content = $body->getContents();
                $params = [];
                parse_str($content, $params);
                $params['api_key'] = $credentials['api_key'];
                $signature = new Signature($params, $credentials['signature_secret'], $credentials['signature_method']);
                $params = $signature->getSignedParams();
                $body->rewind();
                $body->write(http_build_query($params, '', '&'));
                break;
            default:
                $query = [];
                parse_str($request->getUri()->getQuery(), $query);
                $query['api_key'] = $credentials['api_key'];
                $signature = new Signature($query, $credentials['signature_secret'], $credentials['signature_method']);
                $request = $request->withUri(
                    $request->getUri()->withQuery(http_build_query($signature->getSignedParams()))
                );
                break;
        }

        return $request;
    }

    /**
     * @param RequestInterface $request
     * @param Basic $credentials
     * @return RequestInterface
     */
    public static function authRequest(RequestInterface $request, Basic $credentials): RequestInterface
    {
        switch ($request->getHeaderLine('content-type')) {
            case 'application/json':
                if (static::requiresBasicAuth($request)) {
                    $c = $credentials->asArray();
                    $cx = base64_encode($c['api_key'] . ':' . $c['api_secret']);

                    $request = $request->withHeader('Authorization', 'Basic ' . $cx);
                } elseif (static::requiresAuthInUrlNotBody($request)) {
                    $query = [];
                    parse_str($request->getUri()->getQuery(), $query);
                    $query = array_merge($query, $credentials->asArray());

                    $request = $request->withUri($request->getUri()->withQuery(http_build_query($query)));
                } else {
                    $body = $request->getBody();
                    $body->rewind();
                    $content = $body->getContents();
                    $params = json_decode($content, true);

                    if (!$params) {
                        $params = [];
                    }

                    $params = array_merge($params, $credentials->asArray());
                    $body->rewind();
                    $body->write(json_encode($params));
                }
                break;
            case 'application/x-www-form-urlencoded':
                $body = $request->getBody();
                $body->rewind();
                $content = $body->getContents();
                $params = [];
                parse_str($content, $params);
                $params = array_merge($params, $credentials->asArray());
                $body->rewind();
                $body->write(http_build_query($params, '', '&'));
                break;
            default:
                $query = [];
                parse_str($request->getUri()->getQuery(), $query);
                $query = array_merge($query, $credentials->asArray());
                $request = $request->withUri($request->getUri()->withQuery(http_build_query($query)));
                break;
        }

        return $request;
    }

    /**
     * @param array $claims
     * @return Token
     * @throws Exception
     */
    public function generateJwt($claims = []): Token
    {
        if (method_exists($this->credentials, "generateJwt")) {
            return $this->credentials->generateJwt($claims);
        }

        throw new Exception(get_class($this->credentials) . ' does not support JWT generation');
    }

    /**
     * Takes a URL and a key=>value array to generate a GET PSR-7 request object
     *
     * @param string $url The URL to make a request to
     * @param array $params Key=>Value array of data to use as the query string
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function get(string $url, array $params = []): ResponseInterface
    {
        $queryString = '?' . http_build_query($params);
        $url .= $queryString;

        $request = new Request($url, 'GET');

        return $this->send($request);
    }

    /**
     * Takes a URL and a key=>value array to generate a POST PSR-7 request object
     *
     * @param string $url The URL to make a request to
     * @param array $params Key=>Value array of data to send
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function post(string $url, array $params): ResponseInterface
    {
        $request = new Request(
            $url,
            'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($params));

        return $this->send($request);
    }

    /**
     * Takes a URL and a key=>value array to generate a POST PSR-7 request object
     *
     * @param string $url The URL to make a request to
     * @param array $params Key=>Value array of data to send
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function postUrlEncoded(string $url, array $params): ResponseInterface
    {
        $request = new Request(
            $url,
            'POST',
            'php://temp',
            ['content-type' => 'application/x-www-form-urlencoded']
        );

        $request->getBody()->write(http_build_query($params));

        return $this->send($request);
    }

    /**
     * Takes a URL and a key=>value array to generate a PUT PSR-7 request object
     *
     * @param string $url The URL to make a request to
     * @param array $params Key=>Value array of data to send
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function put(string $url, array $params): ResponseInterface
    {
        $request = new Request(
            $url,
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($params));

        return $this->send($request);
    }

    /**
     * Takes a URL and a key=>value array to generate a DELETE PSR-7 request object
     *
     * @param string $url The URL to make a request to
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function delete(string $url): ResponseInterface
    {
        $request = new Request(
            $url,
            'DELETE'
        );

        return $this->send($request);
    }

    /**
     * Wraps the HTTP Client, creates a new PSR-7 request adding authentication, signatures, etc.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        if ($this->credentials instanceof Container) {
            if ($this->needsKeypairAuthentication($request)) {
                $c = $this->credentials->get(Keypair::class)->generateJwt();

                $request = $request->withHeader('Authorization', 'Bearer ' . $c);
            } else {
                $request = self::authRequest($request, $this->credentials->get(Basic::class));
            }
        } elseif ($this->credentials instanceof Keypair) {
            $c = $this->credentials->generateJwt();

            $request = $request->withHeader('Authorization', 'Bearer ' . $c);
        } elseif ($this->credentials instanceof SignatureSecret) {
            $request = self::signRequest($request, $this->credentials);
        } elseif ($this->credentials instanceof Basic) {
            $request = self::authRequest($request, $this->credentials);
        }

        //todo: add oauth support

        //allow any part of the URI to be replaced with a simple search
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
        $request = $request->withHeader('User-Agent', implode(" ", $userAgent));

        return $this->client->sendRequest($request);
    }

    /**
     * @param $app
     */
    protected function validateAppOptions($app): void
    {
        $disallowedCharacters = ['/', ' ', "\t", "\n"];

        foreach (['name', 'version'] as $key) {
            if (!isset($app[$key])) {
                throw new InvalidArgumentException('app.' . $key . ' has not been set');
            }

            foreach ($disallowedCharacters as $char) {
                if (strpos($app[$key], $char) !== false) {
                    throw new InvalidArgumentException('app.' . $key . ' cannot contain the ' . $char . ' character');
                }
            }
        }
    }

    /**
     * @param EntityInterface $entity
     * @return string
     */
    public function serialize(EntityInterface $entity): string
    {
        if ($entity instanceof Verification) {
            return $this->verify()->serialize($entity);
        }

        throw new RuntimeException('unknown class `' . get_class($entity) . '``');
    }

    /**
     * @param $entity
     * @return Verification
     */
    public function unserialize($entity): Verification
    {
        if (is_string($entity)) {
            $entity = unserialize($entity, [Verification::class]);
        }

        if ($entity instanceof Verification) {
            return $this->verify()->unserialize($entity);
        }

        throw new RuntimeException('unknown class `' . get_class($entity) . '``');
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        if (!$this->factory->hasApi($name)) {
            throw new RuntimeException('no api namespace found: ' . $name);
        }

        $collection = $this->factory->getApi($name);

        if (empty($args)) {
            return $collection;
        }

        return call_user_func_array($collection, $args);
    }

    /**
     * @param $name
     * @return mixed
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        if (!$this->factory->hasApi($name)) {
            throw new RuntimeException('no api namespace found: ' . $name);
        }

        return $this->factory->getApi($name);
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    protected static function requiresBasicAuth(RequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        $isSecretManagementEndpoint = strpos($path, '/accounts') === 0 && strpos($path, '/secrets') !== false;
        $isApplicationV2 = strpos($path, '/v2/applications') === 0;

        return $isSecretManagementEndpoint || $isApplicationV2;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    protected static function requiresAuthInUrlNotBody(RequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();

        return strpos($path, '/v1/redact') === 0;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    protected function needsKeypairAuthentication(RequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        $isCallEndpoint = strpos($path, '/v1/calls') === 0;
        $isRecordingUrl = strpos($path, '/v1/files') === 0;
        $isStitchEndpoint = strpos($path, '/beta/conversation') === 0;
        $isUserEndpoint = strpos($path, '/beta/users') === 0;

        return $isCallEndpoint || $isRecordingUrl || $isStitchEndpoint || $isUserEndpoint;
    }

    /**
     * @return string
     */
    protected function getVersion(): string
    {
        return Versions::getVersion('vonage/client-core');
    }
}
