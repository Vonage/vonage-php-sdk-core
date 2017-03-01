<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo;
use Http\Client\HttpClient;
use Nexmo\Client\Credentials\Basic;
use Nexmo\Client\Credentials\Container;
use Nexmo\Client\Credentials\CredentialsInterface;
use Nexmo\Client\Credentials\Keypair;
use Nexmo\Client\Credentials\OAuth;
use Nexmo\Client\Credentials\SharedSecret;
use Nexmo\Client\Factory\FactoryInterface;
use Nexmo\Client\Factory\MapFactory;
use Nexmo\Client\Response\Response;
use Nexmo\Client\Signature;
use Nexmo\Entity\EntityInterface;
use Nexmo\Verify\Verification;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Uri;

/**
 * Nexmo API Client, allows access to the API from PHP.
 *
 * @property \Nexmo\Message\Client $message
 * @property \Nexmo\Call\Collection|\Nexmo\Call\Call[] $calls
 *
 * @method \Nexmo\Message\Client message()
 * @method \Nexmo\Verify\Client  verify()
 * @method \Nexmo\Application\Client applications()
 * @method \Nexmo\Call\Collection calls()
 * @method \Nexmo\Numbers\Client numbers()
 */
class Client
{
    const VERSION = '1.0.0-beta1';

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
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Create a new API client using the provided credentials.
     */
    public function __construct(CredentialsInterface $credentials, $options = array(), HttpClient $client = null)
    {
        if(is_null($client)){
            $client = new \Http\Adapter\Guzzle6\Client();
        }

        $this->setHttpClient($client);

        //make sure we know how to use the credentials
        if(!($credentials instanceof Container) && !($credentials instanceof Basic) && !($credentials instanceof SharedSecret) && !($credentials instanceof OAuth)){
            throw new \RuntimeException('unknown credentials type: ' . get_class($credentials));
        }

        $this->credentials = $credentials;

        $this->options = $options;

        $this->setFactory(new MapFactory([
            'message' => 'Nexmo\Message\Client',
            'verify'  => 'Nexmo\Verify\Client',
            'applications' => 'Nexmo\Application\Client',
            'numbers' => 'Nexmo\Numbers\Client',
            'calls' => 'Nexmo\Call\Collection',
        ], $this));
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
    public function setHttpClient(HttpClient $client)
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

    /**
     * @param RequestInterface $request
     * @param Signature $signature
     * @return RequestInterface
     */
    public static function signRequest(RequestInterface $request, SharedSecret $credentials)
    {
        switch($request->getHeaderLine('content-type')){
            case 'application/json':
                $body = $request->getBody();
                $body->rewind();
                $content = $body->getContents();
                $params = json_decode($content, true);
                $params['api_key'] = $credentials['api_key'];
                $signature = new Signature($params, $credentials['shared_secret']);
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
                $signature = new Signature($params, $credentials['shared_secret']);
                $params = $signature->getSignedParams();
                $body->rewind();
                $body->write(http_build_query($params, null, '&'));
                break;
            default:
                $query = [];
                parse_str($request->getUri()->getQuery(), $query);
                $query['api_key'] = $credentials['api_key'];
                $signature = new Signature($query, $credentials['shared_secret']);
                $request = $request->withUri($request->getUri()->withQuery(http_build_query($signature->getSignedParams())));
                break;
        }

        return $request;
    }

    public static function authRequest(RequestInterface $request, Basic $credentials)
    {
        switch($request->getHeaderLine('content-type')){
            case 'application/json':
                $body = $request->getBody();
                $body->rewind();
                $content = $body->getContents();
                $params = json_decode($content, true);
                $params = array_merge($params, $credentials->asArray());
                $body->rewind();
                $body->write(json_encode($params));
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
     * Wraps the HTTP Client, creates a new PSR-7 request adding authentication, signatures, etc.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(\Psr\Http\Message\RequestInterface $request)
    {
        if($this->credentials instanceof Container) {
            if (strpos($request->getUri()->getPath(), '/v1/calls') === 0) {
                $request = $request->withHeader('Authorization', 'Bearer ' . $this->credentials->get(Keypair::class)->generateJwt());
            } else {
                $request = self::authRequest($request, $this->credentials->get(Basic::class));
            }
        } elseif($this->credentials instanceof Keypair){
            $request = $request->withHeader('Authorization', 'Bearer ' . $this->credentials->get(Keypair::class)->generateJwt());
        } elseif($this->credentials instanceof SharedSecret){
            $request = self::signRequest($request, $this->credentials);
        } elseif($this->credentials instanceof Basic){
            $request = self::authRequest($request, $this->credentials);
        }

        //todo: add oauth support

        //allow any part of the URI to be replaced with a simple search
        if(isset($this->options['url'])){
            foreach($this->options['url'] as $search => $replace){
                $uri = (string) $request->getUri();

                $new = str_replace($search, $replace, $uri);
                if($uri !== $new){
                    $request = $request->withUri(new Uri($new));
                }
            }
        }

        //set the user-agent
        $request = $request->withHeader('user-agent', implode('/', [
            'nexmo-php',
            self::VERSION,
            'PHP-' . implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION])
        ]));

        $response = $this->client->sendRequest($request);
        return $response;
    }

    public function serialize(EntityInterface $entity)
    {
        if($entity instanceof Verification){
            return $this->verify()->serialize($entity);
        }

        throw new \RuntimeException('unknown class `' . get_class($entity) . '``');
    }

    public function unserialize($entity)
    {
        if(is_string($entity)){
            $entity = unserialize($entity);
        }

        if($entity instanceof Verification){
            return $this->verify()->unserialize($entity);
        }

        throw new \RuntimeException('unknown class `' . get_class($entity) . '``');
    }

    public function __call($name, $args)
    {
        if(!$this->factory->hasApi($name)){
            throw new \RuntimeException('no api namespace found: ' . $name);
        }

        $collection = $this->factory->getApi($name);

        if(empty($args)){
            return $collection;
        }

        return call_user_func_array($collection, $args);
    }

    public function __get($name)
    {
        if(!$this->factory->hasApi($name)){
            throw new \RuntimeException('no api namespace found: ' . $name);
        }

        return $this->factory->getApi($name);
    }
}