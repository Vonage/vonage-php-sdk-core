<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest;

use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Message\MessageFactory\DiactorosMessageFactory;
use Http\Mock\Client as HttpMock;
use Nexmo\Client;
use Nexmo\Client\Credentials\Basic;
use Nexmo\Client\Credentials\OAuth;
use Nexmo\Client\Signature;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var HttpMock
     */
    protected $http;

    /**
     * @var Request
     */
    protected $request;

    protected $secret     = 'reallyreallysecret';
    protected $api_key    = 'key12345';
    protected $api_secret = 'secret12345';

    protected $sharedsecret;
    protected $basic;

    public function setUp()
    {
        $this->http         = $this->getMockHttp();
        $this->request      = $this->getRequest();
        $this->sharedsecret = new Client\Credentials\SharedSecret($this->api_key, $this->secret);
        $this->basic        = new Client\Credentials\Basic($this->api_key, $this->api_secret);
    }

    public function testBasicCredentialsQuery()
    {
        $client = new Client($this->basic, [], $this->http);
        $request = $this->getRequest();
        $client->send($request);

        $request = $this->http->getRequests()[0];
        $this->assertRequestQueryContains('api_key', $this->api_key, $request);
        $this->assertRequestQueryContains('api_secret', $this->api_secret, $request);
    }
    
    public function testBasicCredentialsForm()
    {
        $client = new Client($this->basic, [], $this->http);
        $request = $this->getRequest('form');

        $client->send($request);

        $request = $this->http->getRequests()[0];
        $this->assertEmpty($request->getUri()->getQuery());
        $this->assertRequestFormBodyContains('api_key', $this->api_key, $request);
        $this->assertRequestFormBodyContains('api_secret', $this->api_secret, $request);
    }

    public function testBasicCredentialsJson()
    {
        $client = new Client($this->basic, [], $this->http);
        $request = $this->getRequest('json');

        $client->send($request);

        $request = $this->http->getRequests()[0];
        $this->assertEmpty($request->getUri()->getQuery());
        $this->assertRequestJsonBodyContains('api_key', $this->api_key, $request);
        $this->assertRequestJsonBodyContains('api_secret', $this->api_secret, $request);
    }

    public function testOAuthCredentials()
    {
        $client = new Client(new OAuth('ctoken', 'ckey', 'token', 'key'));
        $this->markTestIncomplete('not yet implemented');
    }

    public function testSettingBaseUrl()
    {
        $client = new Client(new Basic('key', 'secret'), [
            'url' => [
                'https://api.nexmo.com' => 'https://proxy.example.com',
                'https://rest.nexmo.com' => 'http://example.com/rest'
            ]
        ], $this->http);

        $client->send(new Request('https://api.nexmo.com/just/path', 'POST'));
        $client->send(new Request('https://rest.nexmo.com/just/path', 'POST'));

        $request = $this->http->getRequests()[0];
        $this->assertSame('proxy.example.com', $request->getUri()->getHost());
        $this->assertSame('/just/path', $request->getUri()->getPath());

        $request = $this->http->getRequests()[1];
        $this->assertSame('example.com', $request->getUri()->getHost());
        $this->assertSame('/rest/just/path', $request->getUri()->getPath());
    }

    public function testSpecificHttpClient()
    {
        $construct = new HttpClient();
        $replace = new HttpClient();

        $client = new Client(new Basic('key', 'secret'), array(), $construct);
        $this->assertSame($construct, $client->getHttpClient());

        $client->setHttpClient($replace);
        $this->assertSame($replace, $client->getHttpClient());
        $this->assertNotSame($construct, $client->getHttpClient());
    }

    public function testSignQueryString()
    {
        $request = $this->getRequest();
        $signed = Client::signRequest($request, $this->sharedsecret);

        $query = [];
        parse_str($signed->getUri()->getQuery(), $query);

        //request should now have signature
        $this->assertValidSignature($query, $this->secret);
        $this->assertRequestQueryContains('api_key', $this->api_key, $signed);
    }

    public function testSignBodyData()
    {
        $request = $this->getRequest('form');
        $signed = Client::signRequest($request, $this->sharedsecret);

        $data = [];
        $signed->getBody()->rewind();
        parse_str($signed->getBody()->getContents(), $data);

        //request should now have signature
        $this->assertRequestFormBodyContains('api_key', $this->api_key, $request);
        $this->assertValidSignature($data, $this->secret);

        //signing should not change query string
        $this->assertEmpty($signed->getUri()->getQuery());
    }

    public function testSignJsonData()
    {
        $request = $this->getRequest('json');
        $signed = Client::signRequest($request, $this->sharedsecret);

        $signed->getBody()->rewind();
        $data = json_decode($signed->getBody()->getContents(), true);
        $this->assertNotNull($data);

        //request should now have signature
        $this->assertRequestJsonBodyContains('api_key', $this->api_key, $request);
        $this->assertValidSignature($data, $this->secret);

        //signing should not change query string
        $this->assertEmpty($signed->getUri()->getQuery());
    }

    public function testBodySignatureDoesNotChangeQuery()
    {
        $client = new Client($this->sharedsecret, [], $this->http);
        $request = $this->getRequest('json');

        $client->send($request);
        $request = $this->http->getRequests()[0];
        $this->assertEmpty($request->getUri()->getQuery());
    }

    public function testSharedSecret()
    {
        $client = new Client($this->sharedsecret, [], $this->http);

        //check that signature is now added to request
        $client->send(new Request('http://example.com?test=value'));
        $request = $this->http->getRequests()[0];

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertValidSignature($query, $this->secret);
    }

    public function testMultipleClients()
    {
        $client1 = new Client(new Basic('key', 'secret'));
        $client2 = new Client(new Basic('key2', 'secret2'));

        $this->assertNotSame($client1, $client2);
    }
    
    public function testSendProxiesClient()
    {
        //get a mock response to test
        $response = new Response();
        $response->getBody()->write('test response');
        $this->http->addResponse($response);

        $client = new Client(new Basic('key', 'secret'), [], $this->http);
        $request = $this->getRequest();

        //api client should simply pass back the http response
        $test = $client->send($request);
        $this->assertSame($response, $test);

        //api client should not change the boy of the request
        $this->assertSame($request->getBody()->getContents(), $this->http->getRequests()[0]->getBody()->getContents());
    }

    /**
     * Any request to a namespaced API ($client->sms) should request that from the factory.
     */
    public function testNamespaceFactory()
    {
        $api = $this->prophesize('stdClass')->reveal();
        
        $factory = $this->prophesize('Nexmo\Client\Factory\FactoryInterface');

        $factory->hasApi('sms')->willReturn(true);
        $factory->getApi('sms')->willReturn($api);
        
        $client = new Client(new Basic('key', 'secret'));
        $client->setFactory($factory->reveal());

        $this->assertSame($api, $client->sms());
    }

    public function testUserAgentString()
    {
        $version = Client::VERSION;
        $php = 'PHP-' . implode('.', [
            PHP_MAJOR_VERSION,
            PHP_MINOR_VERSION
        ]);

        //get a mock response to test
        $response = new Response();
        $response->getBody()->write('test response');
        $this->http->addResponse($response);

        $client = new Client(new Basic('key', 'secret'), [], $this->http);
        $request = $this->getRequest();

        //api client should simply pass back the http response
        $client->send($request);

        //useragent should match the expected format
        $agent = $this->http->getRequests()[0]->getHeaderLine('user-agent');
        $expected = implode('/', [
            'nexmo-php',
            $version,
            $php
        ]);

        $this->assertEquals($expected, $agent);
    }

    /**
     * Allow tests to check that the API client is correctly forming the HTTP request before sending it to the HTTP
     * client.
     *
     * @return HttpMock
     */
    protected function getMockHttp()
    {
        $http = new HttpMock(new DiactorosMessageFactory());
        return $http;
    }

    /**
     * Create a simple PSR-7 request to send through the API client.
     * @return Request
     */
    protected function getRequest($type = 'query', $params = ['name' => 'bob', 'friend' => 'alice'])
    {
        if('query' == $type){
            return new Request('http://example.com?' . http_build_query($params));
        }

        $request = new Request('http://exmaple.com', 'POST');

        switch($type){
            case 'form':
                $body = http_build_query($params, null, '&');
                $request = $request->withHeader('content-type', 'application/x-www-form-urlencoded');
                break;
            case 'json';
                $body = json_encode($params);
                $request = $request->withHeader('content-type', 'application/json');
                break;
            default:
                throw new \RuntimeException('invalid type of response');
        }

        $request->getBody()->write($body);
        return $request;
    }

    public static function assertValidSignature($array, $secret)
    {
        self::assertArrayHasKey('sig', $array);
        self::assertArrayHasKey('timestamp', $array);
        self::assertArrayHasKey('api_key', $array);

        //params should be correctly signed
        $signature = new Signature($array, $secret);
        self::assertTrue($signature->check($array));
    }
}
