<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Application;

use Prophecy\Argument;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Vonage\Application\Client;
use Vonage\Application\Filter;
use Vonage\Client\APIResource;
use Vonage\Application\Hydrator;
use PHPUnit\Framework\TestCase;
use Vonage\Application\RtcConfig;
use VonageTest\Psr7AssertionTrait;
use Vonage\Application\Application;
use Vonage\Application\VoiceConfig;
use Vonage\Application\MessagesConfig;
use Vonage\Client\Exception\Exception;
use Psr\Http\Message\RequestInterface;
use Vonage\Client\Exception\Request as ExceptionRequest;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Entity\Filter\EmptyFilter;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $vonageClient;

    /**
     * @var APIResource
     */
    protected $apiClient;

    /**
     * @var Client
     */
    protected $applicationClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize('Vonage\Client');
        $this->vonageClient->getApiUrl()->willReturn('http://api.nexmo.com');

        $this->applicationClient = new Client();
        $this->applicationClient->setClient($this->vonageClient->reveal());
    }

    public function testSizeException()
    {
        $this->expectException('RuntimeException');
        $this->applicationClient->getSize();
    }

    public function testSetFilter()
    {
        $filter = new Filter(new \DateTime('yesterday'), new \DateTime('tomorrow'));

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

    public function testSetPage()
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

    public function testSetSize()
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

    public function testIterationProperties()
    {
        $this->vonageClient->send(Argument::type(RequestInterface::class))
             ->shouldBeCalledTimes(1)
             ->willReturn($this->getResponse('list'));

        foreach ($this->applicationClient as $id => $application) {
            break;
        }

        $this->assertEquals(7, $this->applicationClient->count());
        $this->assertCount(7, $this->applicationClient);
        $this->assertEquals(2, $this->applicationClient->getPage());
        $this->assertEquals(3, $this->applicationClient->getSize());
    }

    public function testIteratePages()
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
            $this->assertInstanceOf('Vonage\Application\Application', $application);
            $this->assertSame($application->getId(), $id);
        }
    }

    public function testCanIterateClient()
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('list'));

        $this->assertInstanceOf('Iterator', $this->applicationClient);

        foreach ($this->applicationClient as $id => $application) {
            break;
        }

        $this->assertTrue(isset($application));
        $this->assertInstanceOf('Vonage\Application\Application', $application);
        $this->assertSame($application->getId(), $id);
    }

    /**
     * @todo Remove error supression when object passing has been removed
     * @dataProvider getApplication
     */
    public function testGetApplication($payload, $id)
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertEquals('/v2/applications/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse());

        $application = @$this->applicationClient->get($payload);
        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);

        $this->assertInstanceOf('Vonage\Application\Application', $application);
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

    public function getApplication()
    {
        return [
            ['78d335fa323d01149c3dd6f0d48968cf', '78d335fa323d01149c3dd6f0d48968cf'],
            [new Application('78d335fa323d01149c3dd6f0d48968cf'), '78d335fa323d01149c3dd6f0d48968cf']
        ];
    }

    /**
     * @todo Remove error supression when object passing has been removed
     * @todo Rework this whole test, because it uses stock responses it's impossible to test in its current form
     *
     * @dataProvider updateApplication
     */
    public function testUpdateApplication($payload, $method, $id, $expectedId)
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
                            'http_method' => null

                        ],
                        'event_url' => [
                            'address' => 'https://example.com/webhooks/event',
                            'http_method' => null
                        ]
                    ]
                ],
                'rtc' => [
                    'webhooks' => [
                        'event_url' => [
                            'address' => 'https://example.com/webhooks/event',
                            'http_method' => null
                        ]
                    ]
                ],
            ];
            $this->assertRequestJsonBodyContains('capabilities', $capabilities, $request);

            return true;
        }))->willReturn($this->getResponse());

        if ($id) {
            $application = @$this->applicationClient->$method($payload, $id);
        } else {
            $application = @$this->applicationClient->$method($payload);
        }

        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);
        
        $this->assertInstanceOf('Vonage\Application\Application', $application);
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

    public function updateApplication()
    {
        $id = '1a20a124-1775-412b-b623-e6985f4aace0';
        $copy = '1a20a124-1775-412b-4444-e6985f4aace0';
        $existing = new Application($id);
        $existing->setName('My Application');
        @$existing->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/webhooks/answer');
        @$existing->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/webhooks/event');
        @$existing->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/webhooks/event');

        $new = new Application();
        $new->setName('My Application');
        @$new->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/webhooks/answer');
        @$new->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/webhooks/event');
        @$new->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/webhooks/event');

        $raw = [
            'name' => 'My Application',
            'answer_url' => 'https://example.com/webhooks/answer',
            'event_url' => 'https://example.com/webhooks/event'
        ];

        return [
            //can send an application to update it
            [clone $existing, 'update', null, $id],
            //can send raw array and id
            [$raw, 'update', $id, $id],
            //one application overwrites another if id provided
            [clone $existing, 'update', $copy, $copy],
        ];
    }

    /**
     * @dataProvider deleteApplication
     */
    public function testDeleteApplication($payload, $id)
    {
        $this->vonageClient->send(Argument::that(function (Request $request) use ($id) {
            $this->assertEquals('/v2/applications/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('DELETE', $request->getMethod());
            return true;
        }))->willReturn(new Response('php://memory', 204));

        $this->assertTrue(@$this->applicationClient->delete($payload));
    }

    public function deleteApplication()
    {
        return [
            [new Application('abcd1234'), 'abcd1234'],
            ['abcd1234', 'abcd1234'],
        ];
    }

    /**
     * @todo Break this test up, it is doing way too much
     * @dataProvider exceptions
     */
    public function testThrowsException($method, $response, $code)
    {
        $response = $this->getResponse($response, $code);
        $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $application = new Application('78d335fa323d01149c3dd6f0d48968cf');

        try {
            @$this->applicationClient->$method($application);
            $this->fail('did not throw exception');
        } catch (Exception $e) {
            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents(), true);
            $class = substr($code, 0, 1);

            $msg = $data['title'];
            if ($data['detail']) {
                $msg .= ': '.$data['detail'].'. See '.$data['type'].' for more information';
            }

            switch ($class) {
                case '4':
                    $this->assertInstanceOf('Vonage\Client\Exception\Request', $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    break;
                case '5':
                    $this->assertInstanceOf('Vonage\Client\Exception\Server', $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    break;
                default:
                    $this->assertInstanceOf('Vonage\Client\Exception\Exception', $e);
                    $this->assertEquals('Unexpected HTTP Status Code', $e->getMessage());
                    break;
            }
        }
    }

    public function exceptions()
    {
        //todo: add server error
        return [
            //post / create are aliases
            ['update', 'bad', '400'],
            ['update', 'unauthorized', '401'],
            ['create', 'bad', '400'],
            ['create', 'unauthorized', '401'],
            ['delete', 'bad', '400'],
            ['delete', 'unauthorized', '401'],
        ];
    }

    /**
     * @dataProvider createApplication
     */
    public function testCreateApplication($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            $this->assertRequestJsonBodyContains('name', 'My Application', $request);

            // Check for VBC as an object explicitly
            $request->getBody()->rewind();
            $this->assertContains('"vbc":{}', $request->getBody()->getContents());

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
                'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n",
            ];
            $this->assertRequestJsonBodyContains('keys', $keys, $request);
            return true;
        }))->willReturn($this->getResponse('success', '201'));

        $application = @$this->applicationClient->$method($payload);

        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);
        $this->assertInstanceOf('Vonage\Application\Application', $application);
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

    public function createApplication()
    {
        $application = new Application();
        $application->setName('My Application');
        @$application->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/webhooks/answer', 'GET');
        @$application->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/webhooks/event', 'POST');
        @$application->getMessagesConfig()->setWebhook(MessagesConfig::STATUS, 'https://example.com/webhooks/status', 'POST');
        @$application->getMessagesConfig()->setWebhook(MessagesConfig::INBOUND, 'https://example.com/webhooks/inbound', 'POST');
        @$application->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/webhooks/event', 'POST');
        $application->setPublicKey("-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n");
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
            'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n"
        ];

        $rawV2 = [
            'name' => 'My Application',
            'keys' => [
                'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n"
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
            'postApplication' => [clone $application, 'post'],
            'createRawV1' => [$rawV1, 'create'],
            'postRawV1' => [$rawV1, 'post'],
            'createRawV2' => [$rawV2, 'create'],
            'postRawV2' => [$rawV2, 'post'],
        ];
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $status = 200)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'), $status);
    }
}
