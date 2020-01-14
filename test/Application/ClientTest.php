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
use Nexmo\Application\Filter;
use PHPUnit\Framework\TestCase;
use Nexmo\Application\RtcConfig;
use Nexmo\Client\OpenAPIResource;
use NexmoTest\Psr7AssertionTrait;
use Nexmo\Application\Application;
use Nexmo\Application\Hydrator;
use Nexmo\Application\VoiceConfig;
use Nexmo\Application\MessagesConfig;
use Nexmo\Client\Exception\Exception;
use Nexmo\Entity\Hydrator\ArrayHydrator;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var OpenAPIResource
     */
    protected $apiClient;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $applicationClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('http://api.nexmo.com');

        $this->apiClient = new OpenAPIResource();
        $this->apiClient->setCollectionName('applications');
        $this->apiClient->setBaseUri('/v2/applications');
        $this->apiClient->setClient($this->nexmoClient->reveal());

        $hydrator = new Hydrator();

        $this->applicationClient = new Client($this->apiClient, $hydrator);
        $this->applicationClient->setClient($this->nexmoClient->reveal());
    }

    public function testIteratePages()
    {
        $page = $this->getResponse('list');
        $last = $this->getResponse('last');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            //a bit hacky here
            static $last;
            if (is_null($last)) { //first call
                $last = $request;
            }

            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            if ($last !== $request) { //second call
                $this->assertEquals('page_size=3&page_index=3', $request->getUri()->getQuery());
            }

            return true;
        }))->shouldBeCalledTimes(2)->willReturn($page, $last);

        $applications = $this->applicationClient->search();

        foreach ($applications as $application) {
            $this->assertInstanceOf('Nexmo\Application\Application', $application);
        }
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

        $this->assertInstanceOf('Nexmo\Application\Application', $application);
        if ($payload instanceof Application) {
            $this->assertSame($payload, $application);
        }
    }

    public function getApplication()
    {
        return [
            ['1a20a124-1775-412b-b623-e6985f4aace0', '1a20a124-1775-412b-b623-e6985f4aace0'],
        ];
    }

    public function testUpdateApplication()
    {
        $id = '1a20a124-1775-412b-b623-e6985f4aace0';
        $existing = new Application($id);
        $existing->setName('updated application');
        $existing->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/webhooks/answer');
        $existing->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/webhooks/event');
        $existing->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/webhooks/event');

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertEquals('/v2/applications/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('PUT', $request->getMethod());

            $this->assertRequestJsonBodyContains('name', 'updated application', $request);

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

        $application = $this->applicationClient->update($existing);

        $this->assertInstanceOf('Nexmo\Application\Application', $application);
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

        $this->applicationClient->delete($payload);
    }

    public function deleteApplication()
    {
        return [
            [new Application('abcd1234'), 'abcd1234'],
        ];
    }

    /**
     * @dataProvider exceptions
     */
    public function testThrowsException($method, $response, $code)
    {
        $response = $this->getResponse($response, $code);
        $this->nexmoClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $application = new Application('78d335fa323d01149c3dd6f0d48968cf');

        try {
            $this->applicationClient->$method($application);
            $this->fail('did not throw exception');
        } catch (Exception $e) {
            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents(), true);

            $msg = $data['title'];
            if ($data['detail']) {
                $msg .= ': '.$data['detail'].'. See '.$data['type'].' for more information';
            }

            switch ($e->getCode()) {
                case '400':
                    $error = [
                        'type' => 'https://developer.nexmo.com/api-errors/application#payload-validation',
                        'title' => 'Bad Request',
                        'detail' => 'The request failed due to validation errors',
                        'invalid_parameters' => [
                            [
                                'name' => 'capabilities.voice.webhooks.answer_url.http_method',
                                'reason' => 'must be one of: GET, POST'
                            ]
                            ],
                        'instance' => '797a8f199c45014ab7b08bfe9cc1c12c',
                    ];
                    $this->assertSame($error, $e->getEntity());
                    break;
                case '401':
                    $error = [
                        'type' => 'https://developer.nexmo.com/api-errors#unauthorized',
                        'title' => 'Invalid credentials supplied',
                        'detail' => 'You did not provide correct credentials.',
                        'instance' => '797a8f199c45014ab7b08bfe9cc1c12c',
                    ];
                    $this->assertInstanceOf('Nexmo\Client\Exception\Request', $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    $this->assertSame($error, $e->getEntity());
                    break;
            }
        }
    }

    public function exceptions()
    {
        //todo: add server error
        return [
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
                'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n"
            ];
            $this->assertRequestJsonBodyContains('keys', $keys, $request);
            return true;
        }))->willReturn($this->getResponse('success', '201'));

        $application = $this->applicationClient->$method($payload);

        //is an application object was provided, should be the same
        $this->assertInstanceOf('Nexmo\Application\Application', $application);
        if ($payload instanceof Application) {
            $this->assertEquals($payload, $application);
        }
    }

    public function createApplication()
    {
        $application = new Application();
        $application->setName('My Application');
        $application->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/webhooks/answer', 'GET');
        $application->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/webhooks/event', 'POST');
        $application->getMessagesConfig()->setWebhook(MessagesConfig::STATUS, 'https://example.com/webhooks/status', 'POST');
        $application->getMessagesConfig()->setWebhook(MessagesConfig::INBOUND, 'https://example.com/webhooks/inbound', 'POST');
        $application->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/webhooks/event', 'POST');
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
            'createRawV1' => [$rawV1, 'create'],
            'createRawV2' => [$rawV2, 'create'],
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