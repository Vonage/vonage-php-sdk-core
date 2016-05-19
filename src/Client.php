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
use Nexmo\Client\Factory\FactoryInterface;
use Nexmo\Client\Factory\MapFactory;
use Nexmo\Client\Response\Response;
use Nexmo\Client\Signature;
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
     * Secret for Signing Requests
     * @var string
     */
    protected $signatureSecret;

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

        if(isset($options['signature_secret'])){
            $this->signatureSecret = $options['signature_secret'];
        }

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
        return $this->signatureSecret;
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

        //todo: add oauth to request

        //add signature to request
        if($this->signatureSecret){
            $query = [];
            parse_str($request->getUri()->getQuery(), $query);
            $signature = new Signature($query, $this->signatureSecret);
            $request = $request->withUri($request->getUri()->withQuery(http_build_query($signature->getSignedParams())));
        }

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