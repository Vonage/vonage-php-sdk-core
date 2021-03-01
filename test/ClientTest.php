<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest;

use GuzzleHttp\Client as HttpClient;
use Http\Message\MessageFactory\DiactorosMessageFactory;
use Http\Mock\Client as HttpMock;
use InvalidArgumentException;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Factory\FactoryInterface;
use Vonage\Client\Signature;
use Vonage\Verify\Verification;

use function file_get_contents;
use function http_build_query;
use function implode;
use function json_decode;
use function json_encode;
use function parse_str;
use function serialize;
use function set_include_path;

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

    public function setUp(): void
    {
        $this->http = $this->getMockHttp();
        $this->request = $this->getRequest();
        $this->signature_credentials = new Client\Credentials\SignatureSecret($this->api_key, $this->signature_secret);
        $this->basic_credentials = new Client\Credentials\Basic($this->api_key, $this->api_secret);

        set_include_path('app');

        $this->key_credentials = new Client\Credentials\Keypair(
            file_get_contents(
                __DIR__ . '/Client/Credentials/test.key',
                true
            )
        );

        $this->container = new Client\Credentials\Container(
            $this->key_credentials,
            $this->basic_credentials,
            $this->signature_credentials
        );
    }

    /**
     * @dataProvider validateAppNameThrowsProvider
     *
     * @param $name
     * @param $version
     * @param $field
     * @param $invalidCharacter
     */
    public function testValidateAppNameThrows($name, $version, $field, $invalidCharacter): void
    {
        try {
            new Client($this->basic_credentials, [
                'app' => [
                    'name' => $name,
                    'version' => $version
                ]
            ], $this->http);

            self::fail('invalid app details provided, but no exception was thrown');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(
                'app.' . $field . ' cannot contain the ' . $invalidCharacter . ' character',
                $e->getMessage()
            );
        }
    }

    public function validateAppNameThrowsProvider(): array
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

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testBasicCredentialsQuery(): void
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $request = $this->getRequest();
        $client->send($request);
        $request = $this->http->getRequests()[0];

        $this->assertRequestQueryContains('api_key', $this->api_key, $request);
        $this->assertRequestQueryContains('api_secret', $this->api_secret, $request);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testBasicCredentialsForm(): void
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $request = $this->getRequest('form');
        $client->send($request);
        $request = $this->http->getRequests()[0];

        $this->assertEmpty($request->getUri()->getQuery());
        $this->assertRequestFormBodyContains('api_key', $this->api_key, $request);
        $this->assertRequestFormBodyContains('api_secret', $this->api_secret, $request);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCredentialContainerDefaultsBasic(): void
    {
        $client = new Client($this->container, [], $this->http);
        $request = $this->getRequest('json');
        $client->send($request);
        $request = $this->http->getRequests()[0];

        $this->assertEmpty($request->getUri()->getQuery());
        $this->assertRequestJsonBodyContains('api_key', $this->api_key, $request);
        $this->assertRequestJsonBodyContains('api_secret', $this->api_secret, $request);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCredentialContainerUsesKeypairForVoice(): void
    {
        $client = new Client($this->container, [], $this->http);
        $request = $this->getRequest('json', ['test' => 'body'], 'https://api.nexmo.com/v1/calls');
        $client->send($request);
        $request = $this->http->getRequests()[0];

        $this->assertEmpty($request->getUri()->getQuery());

        $auth = $request->getHeaderLine('Authorization');

        $this->assertStringStartsWith('Bearer ', $auth);
        self::markTestIncomplete('Has correct format, but not tested as output of JWT generation');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCredentialContainerUsesKeypairForFiles(): void
    {
        $client = new Client($this->container, [], $this->http);
        $request = $this->getRequest('query', [], 'https://api.nexmo.com/v1/files/AB-12-DC-34');
        $client->send($request);
        $request = $this->http->getRequests()[0];

        $this->assertEmpty($request->getUri()->getQuery());

        $auth = $request->getHeaderLine('Authorization');

        $this->assertStringStartsWith('Bearer ', $auth);
        self::markTestIncomplete('Has correct format, but not tested as output of JWT generation');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testBasicCredentialsJson(): void
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $request = $this->getRequest('json');
        $client->send($request);
        $request = $this->http->getRequests()[0];

        $this->assertEmpty($request->getUri()->getQuery());
        $this->assertRequestJsonBodyContains('api_key', $this->api_key, $request);
        $this->assertRequestJsonBodyContains('api_secret', $this->api_secret, $request);
    }

    public function testOAuthCredentials(): void
    {
        //$client = new Client(new OAuth('ctoken', 'ckey', 'token', 'key'));
        self::markTestSkipped('not yet implemented');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testKeypairCredentials(): void
    {
        $client = new Client($this->key_credentials, [], $this->http);
        $request = $this->getRequest('json');
        $client->send($request);
        $request = $this->http->getRequests()[0];

        $this->assertEmpty($request->getUri()->getQuery());

        $auth = $request->getHeaderLine('Authorization');

        $this->assertStringStartsWith('Bearer ', $auth);
        self::markTestIncomplete('Has correct format, but not tested as output of JWT generation');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testSettingBaseUrl(): void
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

    public function testSpecificHttpClient(): void
    {
        $construct = new HttpClient();
        $replace = new HttpClient();
        $client = new Client(new Basic('key', 'secret'), [], $construct);

        $this->assertSame($construct, $client->getHttpClient());

        $client->setHttpClient($replace);

        $this->assertSame($replace, $client->getHttpClient());
        $this->assertNotSame($construct, $client->getHttpClient());
    }

    /**
     * @throws Client\Exception\Exception
     */
    public function testSignQueryString(): void
    {
        $query = [];
        $request = $this->getRequest();
        $signed = Client::signRequest($request, $this->signature_credentials);

        parse_str($signed->getUri()->getQuery(), $query);

        //request should now have signature
        $this->assertValidSignature($query, $this->signature_secret);
        $this->assertRequestQueryContains('api_key', $this->api_key, $signed);
    }

    /**
     * @throws Client\Exception\Exception
     */
    public function testSignBodyData(): void
    {
        $data = [];
        $request = $this->getRequest('form');
        $signed = Client::signRequest($request, $this->signature_credentials);
        $signed->getBody()->rewind();

        parse_str($signed->getBody()->getContents(), $data);

        //request should now have signature
        $this->assertRequestFormBodyContains('api_key', $this->api_key, $request);
        $this->assertValidSignature($data, $this->signature_secret);

        //signing should not change query string
        $this->assertEmpty($signed->getUri()->getQuery());
    }

    /**
     * @throws Client\Exception\Exception
     */
    public function testSignJsonData(): void
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

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testBodySignatureDoesNotChangeQuery(): void
    {
        $client = new Client($this->signature_credentials, [], $this->http);
        $request = $this->getRequest('json');
        $client->send($request);
        $request = $this->http->getRequests()[0];

        $this->assertEmpty($request->getUri()->getQuery());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testsignatureCredentials(): void
    {
        $query = [];
        $client = new Client($this->signature_credentials, [], $this->http);

        //check that signature is now added to request
        $client->send(new Request('http://example.com?test=value'));
        $request = $this->http->getRequests()[0];

        parse_str($request->getUri()->getQuery(), $query);

        $this->assertValidSignature($query, $this->signature_secret);
    }

    public function testMultipleClients(): void
    {
        $client1 = new Client(new Basic('key', 'secret'));
        $client2 = new Client(new Basic('key2', 'secret2'));

        $this->assertNotSame($client1, $client2);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testSendProxiesClient(): void
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
    public function testNamespaceFactory(): void
    {
        $api = $this->prophesize('stdClass')->reveal();
        /** @var mixed $factory */
        $factory = $this->prophesize(FactoryInterface::class);

        $factory->hasApi('sms')->willReturn(true);
        $factory->getApi('sms')->willReturn($api);

        $client = new Client(new Basic('key', 'secret'));
        $client->setFactory($factory->reveal());

        $this->assertSame($api, $client->sms());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testUserAgentStringAppNotProvided(): void
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
            'vonage-php/' . $version,
            $php
        ]);

        $this->assertEquals($expected, $agent);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testUserAgentStringAppProvided(): void
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
            'vonage-php/' . $version,
            $php,
            'TestApp/9.4.5'
        ]);

        $this->assertEquals($expected, $agent);
    }

    public function testSerializationProxiesVerify(): void
    {
        /** @var mixed $verify */
        $verify = $this->prophesize(\Vonage\Verify\Client::class);
        /** @var mixed $factory */
        $factory = $this->prophesize(FactoryInterface::class);

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
     *
     * @param $url
     * @param $params
     * @param $expected
     *
     * @throws Client\Exception\Exception
     * @throws ClientExceptionInterface
     */
    public function testGenericGetMethod($url, $params, $expected): void
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $client->get($url, $params);
        $request = $this->http->getRequests()[0];

        $this->assertRequestMethod("GET", $request);
        // We can't use assertRequestQueryContains here as $params may be a multi-level array
        $this->assertRequestMatchesUrlWithQueryString($expected, $request);
    }

    public function genericGetProvider(): array
    {
        $baseUrl = 'https://rest.nexmo.com';
        return [
            'simple url, no query string' => [
                $baseUrl . '/example',
                [],
                $baseUrl . '/example'
            ],
            'simple query string' => [
                $baseUrl . '/example',
                [
                    'foo' => 'bar',
                    'a' => 'b'
                ],
                $baseUrl . '/example?foo=bar&a=b'
            ],
            'complex query string' => [
                $baseUrl . '/example',
                ['foo' => ['bar' => 'baz']],
                $baseUrl . '/example?foo%5Bbar%5D=baz'
            ],
            'numeric query string' => [
                $baseUrl . '/example',
                [
                    'a',
                    'b',
                    'c'
                ],
                $baseUrl . '/example?0=a&1=b&2=c'
            ],
        ];
    }

    /**
     * @dataProvider genericPostOrPutProvider
     *
     * @param $url
     * @param $params
     *
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testGenericPostMethod($url, $params): void
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
     *
     * @param $url
     * @param $params
     *
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testGenericPutMethod($url, $params): void
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

    public function genericPostOrPutProvider(): array
    {
        $baseUrl = 'https://rest.nexmo.com';

        return [
            'simple url, no body' => [$baseUrl . '/posting', []],
            'simple body' => [$baseUrl . '/posting', ['foo' => 'bar']],
            'complex body' => [$baseUrl . '/posting', ['foo' => ['bar' => 'baz']]],
            'numeric body' => [$baseUrl . '/posting', ['a', 'b', 'c']],
        ];
    }

    /**
     * @dataProvider genericDeleteProvider
     *
     * @param $url
     * @param $params
     *
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testGenericDeleteMethod($url, $params): void
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        // Delete only takes one parameter, but we test passing two here to make sure that
        // the test breaks if anyone adds support for sending body parameters at a later date.
        // See https://stackoverflow.com/questions/299628/299696#299696
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $client->delete($url, $params);
        $request = $this->http->getRequests()[0];

        $this->assertRequestMethod("DELETE", $request);
        $this->assertRequestBodyIsEmpty($request);
    }

    public function testLoggerIsNullWhenNotSet(): void
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $this->assertNull($client->getLogger());
    }

    public function testCanGetLoggerWhenOneIsSet(): void
    {
        $client = new Client($this->basic_credentials, [], $this->http);
        $logger = $this->prophesize(LoggerInterface::class);
        $client->getFactory()->set(LoggerInterface::class, $logger->reveal());

        $this->assertNotNull($client->getLogger());
    }

    public function genericDeleteProvider(): array
    {
        $baseUrl = 'https://rest.nexmo.com';

        return [
            'simple delete' => [$baseUrl . '/deleting', []],
            'post body must be ignored' => [$baseUrl . '/deleting', ['foo' => 'bar']],
        ];
    }

    /**
     * Allow tests to check that the API client is correctly forming the HTTP request before sending it to the HTTP
     * client.
     */
    protected function getMockHttp(): HttpMock
    {
        return new HttpMock(new DiactorosMessageFactory());
    }

    /**
     * Create a simple PSR-7 request to send through the API client.
     *
     * @param string $type
     * @param string[] $params
     * @param string $url
     */
    protected function getRequest(
        $type = 'query',
        $params = ['name' => 'bob', 'friend' => 'alice'],
        $url = 'http://example.com'
    ): Request {
        if ('query' === $type) {
            return new Request($url . '?' . http_build_query($params));
        }

        $request = new Request($url, 'POST');

        switch ($type) {
            case 'form':
                $body = http_build_query($params, '', '&');
                $request = $request->withHeader('content-type', 'application/x-www-form-urlencoded');
                break;
            case 'json':
                $body = json_encode($params);
                $request = $request->withHeader('content-type', 'application/json');
                break;
            default:
                throw new RuntimeException('invalid type of response');
        }

        $request->getBody()->write($body);
        return $request;
    }

    /**
     * @param $array
     * @param $secret
     *
     * @throws Client\Exception\Exception
     */
    public static function assertValidSignature($array, $secret): void
    {
        self::assertArrayHasKey('sig', $array);
        self::assertArrayHasKey('timestamp', $array);
        self::assertArrayHasKey('api_key', $array);

        //params should be correctly signed
        $signature = new Signature($array, $secret, 'md5hash');

        self::assertTrue($signature->check($array));
    }
}
