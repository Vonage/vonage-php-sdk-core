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
    /**
     * @var HttpMock
     */
    protected $http;

    /**
     * @var Request
     */
    protected $request;

    protected $secret     = 'reallyreallysecret';
    protected $api_key    = 'api_key';
    protected $api_secret = 'api_secret';

    public function setUp()
    {
        $this->http = $this->getMockHttp();
        $this->request = $this->getRequest();
    }

    public function testKeyCredentials()
    {
        $client = new Client(new Basic('key', 'secret'), [], $this->http);
        $client->send($this->getRequest());

        $request = $this->http->getRequests()[0];
        $this->assertQueryContains('api_key', 'key', $request->getUri()->getQuery());
        $this->assertQueryContains('api_secret', 'secret', $request->getUri()->getQuery());
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
        $client = new Client(new Client\Credentials\SharedSecret($this->api_key, $this->secret));

        $params = [
            'name' => 'bob',
            'friend' => 'alice'
        ];

        $request = new Request('http://example.com/?' . http_build_query($params));
        $signed  = $client->signRequest($request);
        
        $query = [];
        parse_str($signed->getUri()->getQuery(), $query);
        
        //request should now have signature
        $this->assertArrayHasKey('sig', $query);
        $this->assertArrayHasKey('timestamp', $query);
        $this->assertArrayHasKey('api_key', $query);

        //params should be correctly signed
        $this->assertEquals($this->api_key, $query['api_key']);
        $signature = new Signature($query, $this->secret);
        $this->assertTrue($signature->check($query));
    }

    public function testSignBodyData()
    {
        $client = new Client(new Client\Credentials\SharedSecret($this->api_key, $this->secret));

        $params = [
            'name' => 'bob',
            'friend' => 'alice'
        ];

        $request = new Request('http://example.com/', 'POST');
        $request = $request->withHeader('content-type', 'application/x-www-form-urlencoded');
        $request->getBody()->write(http_build_query($params, null, '&'));
        
        $signed  = $client->signRequest($request);

        $data = [];
        $signed->getBody()->rewind();
        parse_str($signed->getBody()->getContents(), $data);

        //request should now have signature
        $this->assertArrayHasKey('sig', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('api_key', $data);

        //params should be correctly signed
        $this->assertEquals($this->api_key, $data['api_key']);
        $signature = new Signature($data, $this->secret);
        $this->assertTrue($signature->check($data));
    }

    public function testSignJsonData()
    {
        $client = new Client(new Client\Credentials\SharedSecret($this->api_key, $this->secret));

        $params = [
            'name' => 'bob',
            'friend' => 'alice'
        ];

        $request = new Request('http://example.com/', 'POST');
        $request = $request->withHeader('content-type', 'application/json');
        $request->getBody()->write(json_encode($params));

        $signed  = $client->signRequest($request);

        $signed->getBody()->rewind();
        $data = json_decode($signed->getBody()->getContents(), true);

        $this->assertNotNull($data);

        //request should now have signature
        $this->assertArrayHasKey('sig', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('api_key', $data);

        //params should be correctly signed
        $this->assertEquals($this->api_key, $data['api_key']);
        $signature = new Signature($data, $this->secret);
        $this->assertTrue($signature->check($data));
    }

    public function testSharedSecret()
    {
        $secret = 'reallyreallysecret';

        $client = new Client(new Client\Credentials\SharedSecret($this->api_key, $secret), [], $this->http);

        $this->assertSame($secret, $client->getSignatureSecret());

        //check that signature is now added to request
        $client->send(new Request('http://example.com?test=value'));
        $request = $this->http->getRequests()[0];

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        //request should now have signature
        $this->assertArrayHasKey('sig', $query);
        $this->assertArrayHasKey('timestamp', $query);

        //params should be correctly signed
        $signature = new Signature($query, $secret);
        $this->assertTrue($signature->check($query));
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
    protected function getRequest()
    {
        $request = new Request('http://example.com', 'POST');
        $request->getBody()->write(json_encode(['test' => 'value']));
        return $request;
    }

    public static function assertQueryContains($key, $value, $query)
    {
        $params = [];
        parse_str($query, $params);
        self::assertArrayHasKey($key, $params, 'query string does not have key: ' . $key);
        self::assertSame($value, $params[$key], 'query string does not have value: ' . $value);
    }
}
