<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest;

use GuzzleHttp\Client as HttpClient;
use Http\Message\MessageFactory\DiactorosMessageFactory;
use Http\Mock\Client as HttpMock;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\OAuth;
use Vonage\Client\Signature;
use Vonage\Verify\Verification;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
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

    /**
     * @dataProvider validateAppNameThrowsProvider
     */
    public function testValidateAppNameThrows($name, $version, $field, $invalidCharacter)
    {
        try {
            new Client($this->basic_credentials, [
                'app' => [
                    'name' => $name,
                    'version' => $version
                ]
            ], $this->http);

            $this->fail('invalid app details provided, but no exception was thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('app.'.$field.' cannot contain the '.$invalidCharacter.' character', $e->getMessage());
        }

    }

    public function validateAppNameThrowsProvider()
    {
        $r = [];

        $r['/ name'] = ['foo/bar', '1.0', 'name', '/'];
        $r['space name'] = ['foo bar', '1.0', 'name', ' '];
        $r['tab name'] = ["foo\tbar", '1.0', 'name', "\t"];
        $r['newline name'] = ["foo\nbar", '1.0', 'name', "\n"];

        $r['/ version'] = ['foobar', '1/0', 'version', '/'];
        $r['space version'] = ['foobar', '1 0', 'version', ' '];
        $r['tab version'] = ["foobar", "1\t0", 'version', "\t"];
        $r['newline version'] = ["foobar", "1\n0", 'version', "\n"];

        return $r;
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

    public function testCredentialContainerUsesKeypairForFiles()
    {
        $client = new Client($this->container, [], $this->http);
        $request = $this->getRequest('query', [], 'https://api.nexmo.com/v1/files/AB-12-DC-34');

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

    public function testKeypairCredentials()
    {
        $client = new Client($this->key_credentials, [], $this->http);
        $request = $this->getRequest('json');

        $client->send($request);

        $request = $this->http->getRequests()[0];
        $this->assertEmpty($request->getUri()->getQuery());
        $auth = $request->getHeaderLine('Authorization');
        $this->assertStringStartsWith('Bearer ', $auth);
        $this->markTestIncomplete('Has correct format, but not tested as output of JWT generation');
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
        
        $factory = $this->prophesize('Vonage\Client\Factory\FactoryInterface');

        $factory->hasApi('sms')->willReturn(true);
        $factory->getApi('sms')->willReturn($api);
        
        $client = new Client(new Basic('key', 'secret'));
        $client->setFactory($factory->reveal());

        $this->assertSame($api, $client->sms());
    }

    public function testUserAgentStringAppNotProvided()
    {
        $version = '1.2.3';
        $php = 'php/' . implode('.', [
            PHP_MAJOR_VERSION,
            PHP_MINOR_VERSION
        ]);

        //get a mock response to test
        $response = new Response();
        $response->getBody()->write('test response');
        $this->http->addResponse($response);

        $client = new FixedVersionClient(new Basic('key', 'secret'), [], $this->http);
        $request = $this->getRequest();

        //api client should simply pass back the http response
        $client->send($request);

        //useragent should match the expected format
        $agent = $this->http->getRequests()[0]->getHeaderLine('user-agent');
        $expected = implode(' ', [
            'vonage-php/'.$version,
            $php
        ]);

        $this->assertEquals($expected, $agent);
    }

    public function testUserAgentStringAppProvided()
    {
        $version = '1.2.3';
        $php = 'php/' . implode('.', [
            PHP_MAJOR_VERSION,
            PHP_MINOR_VERSION
        ]);

        //get a mock response to test
        $response = new Response();
        $response->getBody()->write('test response');
        $this->http->addResponse($response);

        $client = new FixedVersionClient(new Basic('key', 'secret'), [
            'app' => [
                'name' => 'TestApp',
                'version' => '9.4.5'
            ]
        ], $this->http);
        $request = $this->getRequest();

        //api client should simply pass back the http response
        $client->send($request);

        //useragent should match the expected format
        $agent = $this->http->getRequests()[0]->getHeaderLine('user-agent');
        $expected = implode(' ', [
            'vonage-php/'.$version,
            $php,
            'TestApp/9.4.5'
        ]);

        $this->assertEquals($expected, $agent);
    }

    public function testSerializationProxiesVerify()
    {
        $verify = $this->prophesize('Vonage\Verify\Client');
        $factory = $this->prophesize('Vonage\Client\Factory\FactoryInterface');

        $factory->hasApi('verify')->willReturn(true);
        $factory->getApi('verify')->willReturn($verify->reveal());

        $client = new Client($this->basic_credentials);
        $client->setFactory($factory->reveal());

        $verification = @new Verification('15554441212', 'test app');
        $verify->serialize($verification)->willReturn('string data')->shouldBeCalled();
        $verify->unserialize($verification)->willReturn($verification)->shouldBeCalled();

        $this->assertEquals('string data', $client->serialize($verification));
        $this->assertEquals($verification, $client->unserialize(serialize($verification)));
    }

    /**
     * @dataProvider genericGetProvider
     */
    public function testGenericGetMethod($url, $params, $expected)
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $request = $client->get($url, $params);

        $request = $this->http->getRequests()[0];
        $this->assertRequestMethod("GET", $request);
        // We can't use assertRequestQueryContains here as $params may be a multi-level array
        $this->assertRequestMatchesUrlWithQueryString($expected, $request);
    }

    public function genericGetProvider()
    {
        $baseUrl = 'https://rest.nexmo.com';
        return [
            'simple url, no query string' => [$baseUrl.'/example', [], $baseUrl.'/example'],
            'simple query string' => [$baseUrl.'/example', ['foo' => 'bar', 'a' => 'b'], $baseUrl.'/example?foo=bar&a=b'],
            'complex query string' => [$baseUrl.'/example', ['foo' => ['bar' => 'baz']], $baseUrl.'/example?foo%5Bbar%5D=baz'],
            'numeric query string' => [$baseUrl.'/example', ['a','b','c'], $baseUrl.'/example?0=a&1=b&2=c'],
        ];
    }

    /**
     * @dataProvider genericPostOrPutProvider
     */
    public function testGenericPostMethod($url, $params)
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $client->post($url, $params);

        // Add our authentication parameters as they'll always be there
        $expectedBody = json_encode($params + [
            'api_key' => 'key12345',
            'api_secret' => 'secret12345'
        ]);

        $request = $this->http->getRequests()[0];
        $this->assertRequestMethod("POST", $request);
        $this->assertRequestMatchesUrl($url, $request);
        $this->assertRequestBodyIsJson($expectedBody, $request);
    }

    /**
     * @dataProvider genericPostOrPutProvider
     */
    public function testGenericPutMethod($url, $params)
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $client->put($url, $params);

        // Add our authentication parameters as they'll always be there
        $expectedBody = json_encode($params + [
                'api_key' => 'key12345',
                'api_secret' => 'secret12345'
            ]);

        $request = $this->http->getRequests()[0];
        $this->assertRequestMethod("PUT", $request);
        $this->assertRequestMatchesUrl($url, $request);
        $this->assertRequestBodyIsJson($expectedBody, $request);
    }

    public function genericPostOrPutProvider()
    {
        $baseUrl = 'https://rest.nexmo.com';
        return [
            'simple url, no body' => [$baseUrl.'/posting', []],
            'simple body' => [$baseUrl.'/posting', ['foo' => 'bar']],
            'complex body' => [$baseUrl.'/posting', ['foo' => ['bar' => 'baz']]],
            'numeric body' => [$baseUrl.'/posting', ['a','b','c']],
        ];
    }

    /**
     * @dataProvider genericDeleteProvider
     */
    public function testGenericDeleteMethod($url, $params)
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        // Delete only takes one parameter, but we test passing two here to make sure that
        // the test breaks if anyone adds support for sending body parameters at a later date.
        // See https://stackoverflow.com/questions/299628/is-an-entity-body-allowed-for-an-http-delete-request/299696#299696
        $client->delete($url, $params);

        $request = $this->http->getRequests()[0];
        $this->assertRequestMethod("DELETE", $request);
        $this->assertRequestBodyIsEmpty($request);
    }

    public function genericDeleteProvider()
    {
        $baseUrl = 'https://rest.nexmo.com';
        return [
            'simple delete' => [$baseUrl.'/deleting', []],
            'post body must be ignored' => [$baseUrl.'/deleting', ['foo' => 'bar']],
        ];
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
        $signature = new Signature($array, $secret, 'md5hash');
        self::assertTrue($signature->check($array));
    }
}

class FixedVersionClient extends Client {
    public function getVersion(){
        return '1.2.3';
    }
}
