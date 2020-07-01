<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Application;

use Prophecy\Argument;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Nexmo\Application\Client;
use Nexmo\Client\APIResource;
use Nexmo\Application\Hydrator;
use PHPUnit\Framework\TestCase;
use Nexmo\Application\RtcConfig;
use NexmoTest\Psr7AssertionTrait;
use Nexmo\Application\Application;
use Nexmo\Application\VoiceConfig;
use Nexmo\Application\MessagesConfig;
use Nexmo\Application\Webhook;
use Nexmo\Client\Exception\Exception;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var APIResource
     */
    protected $apiClient;

    /**
     * @var Client
     */
    protected $applicationClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('http://api.nexmo.com');

        $this->apiClient = new APIResource();
        $this->apiClient
            ->setBaseUri('/v2/applications')
            ->setCollectionName('applications')
            ->setClient($this->nexmoClient->reveal())
        ;

        $this->applicationClient = new Client($this->apiClient, new Hydrator());
        $this->applicationClient->setClient($this->nexmoClient->reveal());
    }

    /**
     * @dataProvider getApplication
     */
    public function testGetApplication($payload, $id)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertEquals('/v2/applications/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse());

        $application = $this->applicationClient->get($payload);
        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);

        $this->assertInstanceOf('Nexmo\Application\Application', $application);
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
     * @todo Rework this whole test, because it uses stock responses it's impossible to test in its current form
     *
     * @dataProvider updateApplication
     */
    public function testUpdateApplication($payload, $method, $id, $expectedId)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($expectedId) {
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

        if ($id) {
            $application = $this->applicationClient->$method($payload, $id);
        } else {
            $application = $this->applicationClient->$method($payload);
        }

        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);
        
        $this->assertInstanceOf('Nexmo\Application\Application', $application);
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
        $existing->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, new Webhook('https://example.com/webhooks/answer'));
        $existing->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, new Webhook('https://example.com/webhooks/event'));
        $existing->getRtcConfig()->setWebhook(RtcConfig::EVENT, new Webhook('https://example.com/webhooks/event'));

        $new = new Application();
        $new->setName('My Application');
        $new->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, new Webhook('https://example.com/webhooks/answer'));
        $new->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, new Webhook('https://example.com/webhooks/event'));
        $new->getRtcConfig()->setWebhook(RtcConfig::EVENT, new Webhook('https://example.com/webhooks/event'));

        $raw = [
            'name' => 'My Application',
            'answer_url' => 'https://example.com/webhooks/answer',
            'event_url' => 'https://example.com/webhooks/event'
        ];

        return [
            //can send an application to update it
            [clone $existing, 'update', null, $id],
        ];
    }

    /**
     * @dataProvider deleteApplication
     */
    public function testDeleteApplication($payload, $id)
    {
        $this->nexmoClient->send(Argument::that(function (Request $request) use ($id) {
            $this->assertEquals('/v2/applications/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('DELETE', $request->getMethod());
            return true;
        }))->willReturn(new Response('php://memory', 204));

        $this->assertTrue($this->applicationClient->delete($payload));
    }

    public function deleteApplication()
    {
        return [
            [new Application('abcd1234'), 'abcd1234'],
        ];
    }

    /**
     * @todo Break this test up, it is doing way too much
     * @dataProvider exceptions
     */
    public function testThrowsException($method, $response, $code)
    {
        $response = $this->getResponse($response, $code);
        $this->nexmoClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $application = new Application('78d335fa323d01149c3dd6f0d48968cf');
        $application->setName('My Application');

        try {
            $this->applicationClient->$method($application);
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
                    $this->assertInstanceOf('Nexmo\Client\Exception\Request', $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    break;
                case '5':
                    $this->assertInstanceOf('Nexmo\Client\Exception\Server', $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    break;
                default:
                    $this->assertInstanceOf('Nexmo\Client\Exception\Exception', $e);
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
        $this->nexmoClient->send(Argument::that(function (Request $request) {
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

        $application = $this->applicationClient->$method($payload);

        $expectedData = json_decode($this->getResponse()->getBody()->getContents(), true);
        $this->assertInstanceOf('Nexmo\Application\Application', $application);
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
        $application->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, new Webhook('https://example.com/webhooks/answer', 'GET'));
        $application->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, new Webhook('https://example.com/webhooks/event', 'POST'));
        $application->getMessagesConfig()->setWebhook(MessagesConfig::STATUS, new Webhook('https://example.com/webhooks/status', 'POST'));
        $application->getMessagesConfig()->setWebhook(MessagesConfig::INBOUND, new Webhook('https://example.com/webhooks/inbound', 'POST'));
        $application->getRtcConfig()->setWebhook(RtcConfig::EVENT, new Webhook('https://example.com/webhooks/event', 'POST'));
        $application->setPublicKey("-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n");
        $application->getVbcConfig()->enable();

        $hydrator = new Hydrator();
        $rawV1 = $hydrator->hydrate([
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
        ]);

        $rawV2 = $hydrator->hydrate([
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
        ]);

        return [
            'createApplication' => [clone $application, 'create'],
            'createv1Application' => [clone $rawV1, 'create'],
            'createv2Application' => [clone $rawV2, 'create'],
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
