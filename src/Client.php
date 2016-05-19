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
use Nexmo\Client\Credentials\CredentialsInterface;
use Nexmo\Client\Credentials\OAuth;
use Nexmo\Client\Credentials\SharedSecret;
use Nexmo\Client\Factory\FactoryInterface;
use Nexmo\Client\Factory\MapFactory;
use Nexmo\Client\Response\Response;
use Nexmo\Client\Signature;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Uri;

/**
 * Nexmo API Client, allows access to the API from PHP.
 *
 * @property \Nexmo\Message\Client $message
 * @method \Nexmo\Message\Client message()
 * @method \Nexmo\Verify\Client  verify()
 */
class Client
{
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
        if(!($credentials instanceof Basic) AND !($credentials instanceof OAuth)){
            throw new \RuntimeException('unknown credentials type: ' . get_class($credentials));
        }

        $this->credentials = $credentials;

        $this->options = $options;

        $this->setFactory(new MapFactory([
            'message' => 'Nexmo\Message\Client',
            'verify'  => 'Nexmo\Verify\Client'
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
     * Get the secret  used for request signing.
     *
     * The client uses it internally to sign outbound requests, but webhooks can be checked
     * using the same secret.
     *
     * @return string
     */
    public function getSignatureSecret()
    {
        if($this->credentials instanceof SharedSecret){
            return $this->credentials['shared_secret'];
        }

        throw new \RuntimeException('can only get signature secret when using `' . SharedSecret::class . '` credentials`');
    }

    /**
     * @param RequestInterface $request
     * @param Signature $signature
     * @return RequestInterface
     */
    public function signRequest(RequestInterface $request)
    {
        switch($request->getHeaderLine('content-type')){
            case 'application/json':
                $body = $request->getBody();
                $body->rewind();
                $content = $body->getContents();
                $params = json_decode($content, true);
                $params['api_key'] = $this->credentials['api_key'];
                $signature = new Signature($params, $this->getSignatureSecret());
                $body->rewind();
                $body->write(json_encode($signature->getSignedParams()));
                break;
            case 'application/x-www-form-urlencoded':
                $body = $request->getBody();
                $body->rewind();
                $content = $body->getContents();
                $params = [];
                parse_str($content, $params);
                $params['api_key'] = $this->credentials['api_key'];
                $signature = new Signature($params, $this->getSignatureSecret());
                $params = $signature->getSignedParams();
                $body->rewind();
                $body->write(http_build_query($params, null, '&'));
                break;
            default:
                $query = [];
                parse_str($request->getUri()->getQuery(), $query);
                $query['api_key'] = $this->credentials['api_key'];
                $signature = new Signature($query, $this->getSignatureSecret());
                $request = $request->withUri($request->getUri()->withQuery(http_build_query($signature->getSignedParams())));
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
        //add key / secret to query string
        if($this->credentials instanceof Basic){
            $query = [];
            parse_str($request->getUri()->getQuery(), $query);
            $query = array_merge($query, $this->credentials->asArray());
            $request = $request->withUri($request->getUri()->withQuery(http_build_query($query)));
        }

        //add signature to request
        if($this->credentials instanceof SharedSecret){
            $request = $this->signRequest($request);
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

        $response = $this->client->sendRequest($request);
        return $response;
    }

    public function __call($name, $args)
    {
        if(!$this->factory->hasApi($name)){
            throw new \RuntimeException('no api namespace found: ' . $name);
        }

        return $this->factory->getApi($name);
    }
}