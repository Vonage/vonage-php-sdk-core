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
use Nexmo\Verify\Verification;
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

    protected $signature_secret = 'reallyreallysecret';
    protected $api_key = 'key12345';
    protected $api_secret = 'secret12345';

    protected $signature_credentials;
    protected $basic_credentials;
    protected $key_credentials;
    protected $container;

    public function setUp()
    {
        $this->http         = $this->getMockHttp();
        $this->request      = $this->getRequest();
        $this->signature_credentials = new Client\Credentials\SignatureSecret($this->api_key, $this->signature_secret);
        $this->basic_credentials        = new Client\Credentials\Basic($this->api_key, $this->api_secret);
        $this->key_credentials          = new Client\Credentials\Keypair(file_get_contents(__DIR__  . '/Client/Credentials/test.key', 'app'));
        $this->container    = new Client\Credentials\Container($this->key_credentials, $this->basic_credentials, $this->signature_credentials);
    }

    public function testBasicCredentialsQuery()
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $request = $this->getRequest();
        $client->send($request);

        $request = $this->http->getRequests()[0];
        $this->assertRequestQueryContains('api_key', $this->api_key, $request);
        $this->assertRequestQueryContains('api_secret', $this->api_secret, $request);
    }
    
    public function testBasicCredentialsForm()
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $request = $this->getRequest('form');

        $client->send($request);

        $request = $this->http->getRequests()[0];
        $this->assertEmpty($request->getUri()->getQuery());
        $this->assertRequestFormBodyContains('api_key', $this->api_key, $request);
        $this->assertRequestFormBodyContains('api_secret', $this->api_secret, $request);
    }

    public function testCredentialContainerDefaultsBasic()
    {
        $client = new Client($this->container, [], $this->http);
        $request = $this->getRequest('json');

        $client->send($request);

        $request = $this->http->getRequests()[0];
        $this->assertEmpty($request->getUri()->getQuery());
        $this->assertRequestJsonBodyContains('api_key', $this->api_key, $request);
        $this->assertRequestJsonBodyContains('api_secret', $this->api_secret, $request);
    }

    public function testCredentialContainerUsesKeypairForVoice()
    {
        $client = new Client($this->container, [], $this->http);
        $request = $this->getRequest('json', ['test' => 'body'], 'https://api.nexmo.com/v1/calls');

        $client->send($request);

        $request = $this->http->getRequests()[0];
        $this->assertEmpty($request->getUri()->getQuery());
        $auth = $request->getHeaderLine('Authorization');
        $this->assertStringStartsWith('Bearer ', $auth);
        $this->markTestIncomplete('Has correct format, but not tested as output of JWT generation');
    }

    public function testBasicCredentialsJson()
    {
        $client = new Client($this->basic_credentials, [], $this->http);
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
        $signed = Client::signRequest($request, $this->signature_credentials);

        $query = [];
        parse_str($signed->getUri()->getQuery(), $query);

        //request should now have signature
        $this->assertValidSignature($query, $this->signature_secret);
        $this->assertRequestQueryContains('api_key', $this->api_key, $signed);
    }

    public function testSignBodyData()
    {
        $request = $this->getRequest('form');
        $signed = Client::signRequest($request, $this->signature_credentials);

        $data = [];
        $signed->getBody()->rewind();
        parse_str($signed->getBody()->getContents(), $data);

        //request should now have signature
        $this->assertRequestFormBodyContains('api_key', $this->api_key, $request);
        $this->assertValidSignature($data, $this->signature_secret);

        //signing should not change query string
        $this->assertEmpty($signed->getUri()->getQuery());
    }

    public function testSignJsonData()
    {
        $request = $this->getRequest('json');
        $signed = Client::signRequest($request, $this->signature_credentials);

        $signed->getBody()->rewind();
        $data = json_decode($signed->getBody()->getContents(), true);
        $this->assertNotNull($data);

        //request should now have signature
        $this->assertRequestJsonBodyContains('api_key', $this->api_key, $request);
        $this->assertValidSignature($data, $this->signature_secret);

        //signing should not change query string
        $this->assertEmpty($signed->getUri()->getQuery());
    }

    public function testBodySignatureDoesNotChangeQuery()
    {
        $client = new Client($this->signature_credentials, [], $this->http);
        $request = $this->getRequest('json');

        $client->send($request);
        $request = $this->http->getRequests()[0];
        $this->assertEmpty($request->getUri()->getQuery());
    }

    public function testsignature_credentials()
    {
        $client = new Client($this->signature_credentials, [], $this->http);

        //check that signature is now added to request
        $client->send(new Request('http://example.com?test=value'));
        $request = $this->http->getRequests()[0];

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertValidSignature($query, $this->signature_secret);
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

    public function testSerializationProxiesVerify()
    {
        $verify = $this->prophesize('Nexmo\Verify\Client');
        $factory = $this->prophesize('Nexmo\Client\Factory\FactoryInterface');

        $factory->hasApi('verify')->willReturn(true);
        $factory->getApi('verify')->willReturn($verify->reveal());

        $client = new Client($this->basic_credentials);
        $client->setFactory($factory->reveal());

        $verification = new Verification('15554441212', 'test app');
        $verify->serialize($verification)->willReturn('string data')->shouldBeCalled();
        $verify->unserialize($verification)->willReturn($verification)->shouldBeCalled();

        $this->assertEquals('string data', $client->serialize($verification));
        $this->assertEquals($verification, $client->unserialize(serialize($verification)));
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
    protected function getRequest($type = 'query', $params = ['name' => 'bob', 'friend' => 'alice'], $url = 'http://example.com')
    {
        if('query' == $type){
            return new Request($url . '?' . http_build_query($params));
        }

        $request = new Request($url, 'POST');

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
