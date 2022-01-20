<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Application;

use DateTime;
use Exception;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Vonage\Application\Webhook;
use Vonage\Application\Webhook as ApplicationWebhook;
use VonageTest\VonageTestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Application\Application;
use Vonage\Application\Client as ApplicationClient;
use Vonage\Application\Filter;
use Vonage\Application\MessagesConfig;
use Vonage\Application\RtcConfig;
use Vonage\Application\VoiceConfig;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Entity\Filter\EmptyFilter;
use VonageTest\Psr7AssertionTrait;

use function fopen;
use function is_null;
use function json_decode;
use function substr;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;

    protected $vonageClient;

    /**
     * @var APIResource
     */
    protected $apiClient;

    /**
     * @var ApplicationClient
     */
    protected $applicationClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('http://api.nexmo.com');

        $this->applicationClient = new ApplicationClient();
        /** @noinspection PhpParamsInspection */
        $this->applicationClient->setClient($this->vonageClient->reveal());
    }

    public function testSizeException(): void
    {
        $this->expectException('RuntimeException');
        $this->applicationClient->getSize();
    }

    /**
     * @throws ClientException
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testSetFilter(): void
    {
        $filter = new Filter(new DateTime('yesterday'), new DateTime('tomorrow'));

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($filter) {
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            foreach ($filter->getQuery() as $key => $value) {
                $this->assertRequestQueryContains($key, $value, $request);
            }

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('list'));

        $this->assertInstanceOf(EmptyFilter::class, $this->applicationClient->getFilter());
        $this->assertSame($this->applicationClient, $this->applicationClient->setFilter($filter));
        $this->assertSame($filter, $this->applicationClient->getFilter());

        $this->applicationClient->rewind();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Request
     * @throws ClientException
     * @throws ServerException
     */
    public function testSetPage(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('page_index', '1', $request);
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('list'));

        $this->assertSame($this->applicationClient, $this->applicationClient->setPage(1));
        $this->assertEquals(1, $this->applicationClient->getPage());

        $this->applicationClient->rewind();
    }

    public function testCannotCreateApplicationClientWithId(): void
    {
        $this->expectException(\TypeError::class);
        $applicationClient = new ApplicationClient('78d335fa323d01149c3dd6f0d48968cf');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Request
     * @throws ClientException
     * @throws ServerException
     */
    public function testSetSize(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            $this->assertRequestQueryContains('page_size', '5', $request);
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('list'));

        $this->assertSame($this->applicationClient, $this->applicationClient->setSize(5));
        $this->assertEquals(5, $this->applicationClient->getSize());

        $this->applicationClient->rewind();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Request
     * @throws ClientException
     * @throws ServerException
     */
    public function testIterationProperties(): void
    {
        $this->vonageClient->send(Argument::type(RequestInterface::class))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->getResponse('list'));

        $this->assertEquals(7, $this->applicationClient->count());
        $this->assertCount(7, $this->applicationClient);
        $this->assertEquals(2, $this->applicationClient->getPage());
        $this->assertEquals(3, $this->applicationClient->getSize());
    }

    public function testIteratePages(): void
    {
        $page = $this->getResponse('list');
        $last = $this->getResponse('last');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            //a bit hacky here
            static $last;
            if (is_null($last)) { //first call
                $last = $request;
            }

            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            if ($last !== $request) { //second call
                $this->assertRequestQueryContains('page_size', '3', $request);
                $this->assertRequestQueryContains('page_index', '3', $request);
            }

            return true;
        }))->shouldBeCalledTimes(2)->willReturn($page, $last);

        foreach ($this->applicationClient as $id => $application) {
            $this->assertInstanceOf(Application::class, $application);
            $this->assertSame($application->getId(), $id);
        }
    }

    public function testCanIterateClient(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('list'));

        $this->assertInstanceOf('Iterator', $this->applicationClient);

        $application = $id = null;

        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->applicationClient as $id => $application) {
            break;
        }

        $this->assertTrue(isset($application));
        $this->assertInstanceOf(Application::class, $application);
        $this->assertSame($application->getId(), $id);
    }

    /**
     * @dataProvider getApplication
     *
     * @param $payload
     * @param $id
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws Exception
     */
    public function testGetApplication($payload, $id, $expectsError): void
    {
        if ($expectsError) {
            $this->expectError();
        }

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertEquals('/v2/applications/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse());

        $application = @$this->applicationClient->get($payload);
        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);

        $this->assertInstanceOf(Application::class, $application);
        $this->assertSame($expectedData['id'], $application->getId());
        $this->assertSame($expectedData['name'], $application->getName());
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['answer_url']['address'],
            $application->getVoiceConfig()->getWebhook('answer_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['answer_url']['http_method'],
            $application->getVoiceConfig()->getWebhook('answer_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['event_url']['address'],
            $application->getVoiceConfig()->getWebhook('event_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['event_url']['http_method'],
            $application->getVoiceConfig()->getWebhook('event_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['inbound_url']['address'],
            $application->getMessagesConfig()->getWebhook('inbound_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['inbound_url']['http_method'],
            $application->getMessagesConfig()->getWebhook('inbound_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['status_url']['address'],
            $application->getMessagesConfig()->getWebhook('status_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['status_url']['http_method'],
            $application->getMessagesConfig()->getWebhook('status_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
            $application->getRtcConfig()->getWebhook('event_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
            $application->getRtcConfig()->getWebhook('event_url')->getUrl()
        );

        self::markTestIncomplete('Remove error suppression when object passing has been removed');
    }

    public function getApplication(): array
    {
        return [
            ['78d335fa323d01149c3dd6f0d48968cf', '78d335fa323d01149c3dd6f0d48968cf', false],
            [new Application('78d335fa323d01149c3dd6f0d48968cf'), '78d335fa323d01149c3dd6f0d48968cf', true]
        ];
    }

    /**
     * @dataProvider updateApplication
     *
     * @param $payload
     * @param $method
     * @param $id
     * @param $expectedId
     */
    public function testUpdateApplication($payload, $method, $expectedId): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedId) {
            $this->assertEquals('/v2/applications/' . $expectedId, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('PUT', $request->getMethod());

            $this->assertRequestJsonBodyContains('name', 'My Application', $request);

            // And check all other capabilities
            $capabilities = [
                'voice' => [
                    'webhooks' => [
                        'answer_url' => [
                            'address' => 'https://example.com/webhooks/answer',
                            'http_method' => 'POST'

                        ],
                        'event_url' => [
                            'address' => 'https://example.com/webhooks/event',
                            'http_method' => 'POST'
                        ]
                    ]
                ],
                'rtc' => [
                    'webhooks' => [
                        'event_url' => [
                            'address' => 'https://example.com/webhooks/event',
                            'http_method' => 'POST'
                        ]
                    ]
                ],
            ];
            $this->assertRequestJsonBodyContains('capabilities', $capabilities, $request);

            return true;
        }))->willReturn($this->getResponse());

        $application = $this->applicationClient->$method($payload);

        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);

        $this->assertInstanceOf(Application::class, $application);
        $this->assertSame($expectedData['id'], $application->getId());
        $this->assertSame($expectedData['name'], $application->getName());
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['answer_url']['address'],
            $application->getVoiceConfig()->getWebhook('answer_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['answer_url']['http_method'],
            $application->getVoiceConfig()->getWebhook('answer_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['event_url']['address'],
            $application->getVoiceConfig()->getWebhook('event_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['event_url']['http_method'],
            $application->getVoiceConfig()->getWebhook('event_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['inbound_url']['address'],
            $application->getMessagesConfig()->getWebhook('inbound_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['inbound_url']['http_method'],
            $application->getMessagesConfig()->getWebhook('inbound_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['status_url']['address'],
            $application->getMessagesConfig()->getWebhook('status_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['status_url']['http_method'],
            $application->getMessagesConfig()->getWebhook('status_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
            $application->getRtcConfig()->getWebhook('event_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
            $application->getRtcConfig()->getWebhook('event_url')->getUrl()
        );
    }

    /**
     * @throws Exception
     *
     * @return array[]
     */
    public function updateApplication(): array
    {
        $answerWebhook = new ApplicationWebhook('https://example.com/webhooks/answer');
        $eventWebhook = new ApplicationWebhook('https://example.com/webhooks/event');

        $id = '1a20a124-1775-412b-b623-e6985f4aace0';
        $existing = new Application($id);
        $existing->setName('My Application');
        $existing->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, $answerWebhook);
        $existing->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, $eventWebhook);
        $existing->getRtcConfig()->setWebhook(RtcConfig::EVENT, $eventWebhook);

        $new = new Application();
        $new->setName('My Application');
        $new->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, $answerWebhook);
        $new->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, $eventWebhook);
        $new->getRtcConfig()->setWebhook(RtcConfig::EVENT, $eventWebhook);

        return [
            [clone $existing, 'update', $id]
        ];
    }

    /**
     * @dataProvider deleteApplication
     *
     * @param $payload
     * @param $id
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function testDeleteApplication($payload, $id): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) use ($id) {
            $this->assertEquals('/v2/applications/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('DELETE', $request->getMethod());
            return true;
        }))->willReturn(new Response('php://memory', 204));

        $this->assertTrue(@$this->applicationClient->delete($payload));
    }

    public function testCannotDeleteApplicationByPassingApplicationObject(): void
    {
        $this->expectException(\TypeError::class);
        $application = new Application();
        $applicationClient = new ApplicationClient($application);
    }

    public function deleteApplication(): array
    {
        return [
            ['abcd1234', 'abcd1234']
        ];
    }

    /**
     * @dataProvider exceptions
     *
     * @param $method
     * @param $response
     * @param $code
     */
    public function testThrowsException($method, $response, $code): void
    {
        $response = $this->getResponse($response, $code);
        $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $application = new Application('78d335fa323d01149c3dd6f0d48968cf');

        try {
            @$this->applicationClient->$method($application);

            self::fail('did not throw exception');
        } catch (ClientException $e) {
            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents(), true);
            $class = substr((string)$code, 0, 1);

            $msg = $data['title'];
            if ($data['detail']) {
                $msg .= ': ' . $data['detail'] . '. See ' . $data['type'] . ' for more information';
            }

            switch ($class) {
                case '4':
                    $this->assertInstanceOf(Client\Exception\Request::class, $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    break;
                case '5':
                    $this->assertInstanceOf(ServerException::class, $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    break;
                default:
                    $this->assertInstanceOf(ClientException::class, $e);
                    $this->assertEquals('Unexpected HTTP Status Code', $e->getMessage());
                    break;
            }
        }

        self::markTestIncomplete('Break this test up, it is doing way too much');
    }

    /**
     * @return string[]
     */
    public function exceptions(): array
    {
        //todo: add server error
        return [
            //post / create are aliases
            ['update', 'bad', 400],
            ['update', 'unauthorized', 401],
            ['create', 'bad', 400],
            ['create', 'unauthorized', 401],
        ];
    }

    /**
     * @dataProvider createApplication
     *
     * @param $payload
     * @param $method
     */
    public function testCreateApplication($payload, $method): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            $this->assertRequestJsonBodyContains('name', 'My Application', $request);

            // Check for VBC as an object explicitly
            $request->getBody()->rewind();
            $this->assertStringContainsString('"vbc":{}', $request->getBody()->getContents());

            // And check all other capabilities
            $capabilities = [
                'voice' => [
                    'webhooks' => [
                        'answer_url' => [
                            'address' => 'https://example.com/webhooks/answer',
                            'http_method' => 'GET'

                        ],
                        'event_url' => [
                            'address' => 'https://example.com/webhooks/event',
                            'http_method' => 'POST'
                        ]
                    ]
                ],
                'messages' => [
                    'webhooks' => [
                        'inbound_url' => [
                            'address' => 'https://example.com/webhooks/inbound',
                            'http_method' => 'POST'

                        ],
                        'status_url' => [
                            'address' => 'https://example.com/webhooks/status',
                            'http_method' => 'POST'
                        ]
                    ]
                ],
                'rtc' => [
                    'webhooks' => [
                        'event_url' => [
                            'address' => 'https://example.com/webhooks/event',
                            'http_method' => 'POST',
                        ],
                    ]
                ],
                'vbc' => []
            ];
            $this->assertRequestJsonBodyContains('capabilities', $capabilities, $request);

            // And the public key
            $keys = [
                'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjx" .
                    "u33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xs" .
                    "O\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n",
            ];
            $this->assertRequestJsonBodyContains('keys', $keys, $request);
            return true;
        }))->willReturn($this->getResponse('success', 201));

        $application = @$this->applicationClient->$method($payload);

        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);
        $this->assertInstanceOf(Application::class, $application);
        $this->assertSame($expectedData['id'], $application->getId());
        $this->assertSame($expectedData['name'], $application->getName());
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['answer_url']['address'],
            $application->getVoiceConfig()->getWebhook('answer_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['answer_url']['http_method'],
            $application->getVoiceConfig()->getWebhook('answer_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['event_url']['address'],
            $application->getVoiceConfig()->getWebhook('event_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['voice']['webhooks']['event_url']['http_method'],
            $application->getVoiceConfig()->getWebhook('event_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['inbound_url']['address'],
            $application->getMessagesConfig()->getWebhook('inbound_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['inbound_url']['http_method'],
            $application->getMessagesConfig()->getWebhook('inbound_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['status_url']['address'],
            $application->getMessagesConfig()->getWebhook('status_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['messages']['webhooks']['status_url']['http_method'],
            $application->getMessagesConfig()->getWebhook('status_url')->getMethod()
        );
        $this->assertSame(
            $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
            $application->getRtcConfig()->getWebhook('event_url')->getUrl()
        );
        $this->assertSame(
            $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
            $application->getRtcConfig()->getWebhook('event_url')->getUrl()
        );
    }

    /**
     * @throws Exception
     *
     * @return array[]
     */
    public function createApplication(): array
    {
        $application = new Application();
        $application->setName('My Application');

        $answerWebhook = new Webhook('https://example.com/webhooks/answer', 'GET');
        $eventWebhook = new Webhook('https://example.com/webhooks/event', 'POST');
        $statusWebhook = new Webhook('https://example.com/webhooks/status', 'POST');
        $inboundWebhook = new Webhook('https://example.com/webhooks/inbound', 'POST');

        $application->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, $answerWebhook);
        $application->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, $eventWebhook);
        $application->getMessagesConfig()->setWebhook(MessagesConfig::STATUS, $statusWebhook);
        $application->getMessagesConfig()->setWebhook(MessagesConfig::INBOUND, $inboundWebhook);

        $application->getRtcConfig()->setWebhook(RtcConfig::EVENT, $eventWebhook);

        $application->setPublicKey("-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSL" .
            "cjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOU" .
            "NhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n");
        $application->getVbcConfig()->enable();

        $rawV1 = [
            'name' => 'My Application',
            'answer_url' => 'https://example.com/webhooks/answer',
            'answer_method' => 'GET',
            'event_url' => 'https://example.com/webhooks/event',
            'event_method' => 'POST',
            'status_url' => 'https://example.com/webhooks/status',
            'status_method' => 'POST',
            'inbound_url' => 'https://example.com/webhooks/inbound',
            'inbound_method' => 'POST',
            'vbc' => true,
            'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G" .
                "\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhP" .
                "x2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n"
        ];

        $rawV2 = [
            'name' => 'My Application',
            'keys' => [
                'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjx" .
                    "u33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xs" .
                    "O\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n"
            ],
            'capabilities' => [
                'voice' => [
                    'webhooks' => [
                        'answer_url' => [
                            'address' => 'https://example.com/webhooks/answer',
                            'http_method' => 'GET',
                        ],
                        'event_url' => [
                            'address' => 'https://example.com/webhooks/event',
                            'http_method' => 'POST',
                        ],
                    ]
                ],
                'messages' => [
                    'webhooks' => [
                        'inbound_url' => [
                            'address' => 'https://example.com/webhooks/inbound',
                            'http_method' => 'POST'

                        ],
                        'status_url' => [
                            'address' => 'https://example.com/webhooks/status',
                            'http_method' => 'POST'
                        ]
                    ]
                ],
                'rtc' => [
                    'webhooks' => [
                        'event_url' => [
                            'address' => 'https://example.com/webhooks/event',
                            'http_method' => 'POST',
                        ],
                    ]
                ],
                'vbc' => []
            ]
        ];

        return [
            'createApplication' => [clone $application, 'create'],
        ];
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
