<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage;

use Zend\Diactoros\Uri;
use Http\Client\HttpClient;
use Vonage\Client\Signature;
use Zend\Diactoros\Request;
use Vonage\Client\APIResource;
use Vonage\Verify\Verification;
use Vonage\Entity\EntityInterface;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\OAuth;
use Vonage\Client\Factory\MapFactory;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Exception\Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Factory\FactoryInterface;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Credentials\CredentialsInterface;
use Psr\Http\Client\ClientInterface;

/**
 * Vonage API Client, allows access to the API from PHP.
 *
 * @method \Vonage\Account\Client account()
 * @method \Vonage\Message\Client message()
 * @method \Vonage\Application\Client applications()
 * @method \Vonage\Conversion\Client conversion()
 * @method \Vonage\Insights\Client insights()
 * @method \Vonage\Numbers\Client numbers()
 * @method \Vonage\Redact\Client redact()
 * @method \Vonage\SMS\Client sms()
 * @method \Vonage\Verify\Client  verify()
 * @method \Vonage\Voice\Client voice()
 */
class Client
{
    const VERSION = '2.2.0';

    const BASE_API  = 'https://api.nexmo.com';
    const BASE_REST = 'https://rest.nexmo.com';

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
     */
    public function __construct(CredentialsInterface $credentials, $options = array(), ClientInterface $client = null)
    {
        if (is_null($client)) {
            $client = new \Http\Adapter\Guzzle6\Client();
        }

        $this->setHttpClient($client);

        //make sure we know how to use the credentials
        if (!($credentials instanceof Container) && !($credentials instanceof Basic) && !($credentials instanceof SignatureSecret) && !($credentials instanceof OAuth) && !($credentials instanceof Keypair)) {
            throw new \RuntimeException('unknown credentials type: ' . get_class($credentials));
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
            'message' => \Vonage\Message\Client::class,
            'calls' => \Vonage\Call\Collection::class,
            'conversation' => \Vonage\Conversations\Collection::class,
            'user' => \Vonage\User\Collection::class,

            // Registered Services by name
            'account' => \Vonage\Account\ClientFactory::class,
            'applications' => \Vonage\Application\ClientFactory::class,
            'conversion' => \Vonage\Conversion\ClientFactory::class,
            'insights' => \Vonage\Insights\ClientFactory::class,
            'numbers' => \Vonage\Numbers\ClientFactory::class,
            'redact' => \Vonage\Redact\ClientFactory::class,
            'sms' => \Vonage\SMS\ClientFactory::class,
            'verify' => \Vonage\Verify\ClientFactory::class,
            'voice' => \Vonage\Voice\ClientFactory::class,

            // Additional utility classes
            APIResource::class => APIResource::class,
        ], $this));

        // Disable throwing E_USER_DEPRECATED notices by default, the user can turn it on during development
        if (array_key_exists('show_deprecations', $this->options) && !$this->options['show_deprecations']) {
            set_error_handler(
                function (
                    int $errno,
                    string $errstr,
                    string $errfile,
                    int $errline,
                    array $errorcontext
                ) {
                    return true;
                },
                E_USER_DEPRECATED
            );
        }
    }

    public function getRestUrl()
    {
        return $this->restUrl;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Set the Http Client to used to make API requests.
     *
     * This allows the default http client to be swapped out for a HTTPlug compatible
     * replacement.
     *
     * @param HttpClient $client
     * @return $this
     */
    public function setHttpClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get the Http Client used to make API requests.
     *
     * @return HttpClient
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
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    public function getFactory() : ContainerInterface
    {
        return $this->factory;
    }

    /**
     * @param RequestInterface $request
     * @param Signature $signature
     * @return RequestInterface
     */
    public static function signRequest(RequestInterface $request, SignatureSecret $credentials)
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
                $body->write(http_build_query($params, null, '&'));
                break;
            default:
                $query = [];
                parse_str($request->getUri()->getQuery(), $query);
                $query['api_key'] = $credentials['api_key'];
                $signature = new Signature($query, $credentials['signature_secret'], $credentials['signature_method']);
                $request = $request->withUri($request->getUri()->withQuery(http_build_query($signature->getSignedParams())));
                break;
        }

        return $request;
    }

    public static function authRequest(RequestInterface $request, Basic $credentials)
    {
        switch ($request->getHeaderLine('content-type')) {
            case 'application/json':
                if (static::requiresBasicAuth($request)) {
                    $c = $credentials->asArray();
                    $request = $request->withHeader('Authorization', 'Basic ' . base64_encode($c['api_key'] . ':' . $c['api_secret']));
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
                $body->write(http_build_query($params, null, '&'));
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
     * @return \Lcobucci\JWT\Token
     */
    public function generateJwt($claims = [])
    {
        if (method_exists($this->credentials, "generateJwt")) {
            return $this->credentials->generateJwt($claims);
        }
        throw new Exception(get_class($this->credentials).' does not support JWT generation');
    }
    
    /**
     * Takes a URL and a key=>value array to generate a GET PSR-7 request object
     *
     * @param string $url The URL to make a request to
     * @param array $params Key=>Value array of data to use as the query string
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($url, array $params = [])
    {
        $queryString = '?' . http_build_query($params);

        $url = $url . $queryString;

        $request = new Request(
            $url,
            'GET'
        );

        return $this->send($request);
    }

    /**
     * Takes a URL and a key=>value array to generate a POST PSR-7 request object
     *
     * @param string $url The URL to make a request to
     * @param array $params Key=>Value array of data to send
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($url, array $params)
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function postUrlEncoded($url, array $params)
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($url, array $params)
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($url)
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
    * @param \Psr\Http\Message\RequestInterface $request
    * @return \Psr\Http\Message\ResponseInterface
    */
    public function send(\Psr\Http\Message\RequestInterface $request)
    {
        if ($this->credentials instanceof Container) {
            if ($this->needsKeypairAuthentication($request)) {
                $request = $request->withHeader('Authorization', 'Bearer ' . $this->credentials->get(Keypair::class)->generateJwt());
            } else {
                $request = self::authRequest($request, $this->credentials->get(Basic::class));
            }
        } elseif ($this->credentials instanceof Keypair) {
            $request = $request->withHeader('Authorization', 'Bearer ' . $this->credentials->generateJwt());
        } elseif ($this->credentials instanceof SignatureSecret) {
            $request = self::signRequest($request, $this->credentials);
        } elseif ($this->credentials instanceof Basic) {
            $request = self::authRequest($request, $this->credentials);
        }

        //todo: add oauth support

        //allow any part of the URI to be replaced with a simple search
        if (isset($this->options['url'])) {
            foreach ($this->options['url'] as $search => $replace) {
                $uri = (string) $request->getUri();

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
        $userAgent[] = 'vonage-php/'.$this->getVersion();

        // Language name
        $userAgent[] = 'php/'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;

        // If we have an app set, add that to the UA
        if (isset($this->options['app'])) {
            $app = $this->options['app'];
            $userAgent[] = $app['name'].'/'.$app['version'];
        }

        // Set the header. Build by joining all the parts we have with a space
        $request = $request->withHeader('User-Agent', implode(" ", $userAgent));

        $response = $this->client->sendRequest($request);
        return $response;
    }

    protected function validateAppOptions($app)
    {
        $disallowedCharacters = ['/', ' ', "\t", "\n"];
        foreach (['name', 'version'] as $key) {
            if (!isset($app[$key])) {
                throw new \InvalidArgumentException('app.'.$key.' has not been set');
            }

            foreach ($disallowedCharacters as $char) {
                if (strpos($app[$key], $char) !== false) {
                    throw new \InvalidArgumentException('app.'.$key.' cannot contain the '.$char.' character');
                }
            }
        }
    }

    public function serialize(EntityInterface $entity)
    {
        if ($entity instanceof Verification) {
            return $this->verify()->serialize($entity);
        }

        throw new \RuntimeException('unknown class `' . get_class($entity) . '``');
    }

    public function unserialize($entity)
    {
        if (is_string($entity)) {
            $entity = unserialize($entity);
        }

        if ($entity instanceof Verification) {
            return $this->verify()->unserialize($entity);
        }

        throw new \RuntimeException('unknown class `' . get_class($entity) . '``');
    }

    public function __call($name, $args)
    {
        if (!$this->factory->hasApi($name)) {
            throw new \RuntimeException('no api namespace found: ' . $name);
        }

        $collection = $this->factory->getApi($name);

        if (empty($args)) {
            return $collection;
        }

        return call_user_func_array($collection, $args);
    }

    public function __get($name)
    {
        if (!$this->factory->has($name)) {
            throw new \RuntimeException('no api namespace found: ' . $name);
        }

        return $this->factory->get($name);
    }

    protected static function requiresBasicAuth(\Psr\Http\Message\RequestInterface $request)
    {
        $path = $request->getUri()->getPath();
        $isSecretManagementEndpoint = strpos($path, '/accounts') === 0 && strpos($path, '/secrets') !== false;
        $isApplicationV2 = strpos($path, '/v2/applications') === 0;

        return $isSecretManagementEndpoint || $isApplicationV2;
    }

    protected static function requiresAuthInUrlNotBody(\Psr\Http\Message\RequestInterface $request)
    {
        $path = $request->getUri()->getPath();
        $isRedactEndpoint = strpos($path, '/v1/redact') === 0;

        return $isRedactEndpoint;
    }

    protected function needsKeypairAuthentication(\Psr\Http\Message\RequestInterface $request)
    {
        $path = $request->getUri()->getPath();
        $isCallEndpoint = strpos($path, '/v1/calls') === 0;
        $isRecordingUrl = strpos($path, '/v1/files') === 0;
        $isStitchEndpoint = strpos($path, '/beta/conversation') === 0;
        $isUserEndpoint = strpos($path, '/beta/users') === 0;

        return $isCallEndpoint || $isRecordingUrl || $isStitchEndpoint || $isUserEndpoint;
    }

    protected function getVersion()
    {
        return \PackageVersions\Versions::getVersion('vonage/client-core');
    }
}
