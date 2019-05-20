<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Application;

use Nexmo\Application\Application;
use Nexmo\Application\Client;
use Nexmo\Application\Filter;
use Nexmo\Application\MessagesConfig;
use Nexmo\Application\RtcConfig;
use Nexmo\Application\VoiceConfig;
use Nexmo\Client\Exception\Exception;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Psr\Http\Message\RequestInterface;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $applicationClient;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('http://api.nexmo.com');
        $this->applicationClient = new Client();
        $this->applicationClient->setClient($this->nexmoClient->reveal());
    }

    public function testPageException()
    {
        $this->expectException('RuntimeException');
        $this->applicationClient->getPage();
    }

    public function testSizeException()
    {
        $this->expectException('RuntimeException');
        $this->applicationClient->getSize();
    }

    public function testSetFilter()
    {
        $filter = new Filter(new \DateTime('yesterday'), new \DateTime('tomorrow'));

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($filter){
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            foreach($filter->getQuery() as $key => $value){
                $this->assertRequestQueryContains($key, $value, $request);
            }

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('list'));

        $this->assertInstanceOf('Nexmo\Entity\EmptyFilter', $this->applicationClient->getFilter());
        $this->assertSame($this->applicationClient, $this->applicationClient->setFilter($filter));
        $this->assertSame($filter, $this->applicationClient->getFilter());

        $this->applicationClient->rewind();
    }

    public function testSetPage()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) {
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
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) {
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
        $this->nexmoClient->send(Argument::type(RequestInterface::class))
             ->shouldBeCalledTimes(1)
             ->willReturn($this->getResponse('list'));

        foreach($this->applicationClient as $id => $application)
        {
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

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) {
            //a bit hacky here
            static $last;
            if(is_null($last)){ //first call
                $last = $request;
            }

            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());

            if($last !== $request){ //second call
                $this->assertEquals('page_size=3&page_index=3', $request->getUri()->getQuery());
            }

            return true;
        }))->shouldBeCalledTimes(2)->willReturn($page, $last);


        foreach($this->applicationClient as $id => $application)
        {
            $this->assertInstanceOf('Nexmo\Application\Application', $application);
            $this->assertSame($application->getId(), $id);
        }
    }

    public function testCanIterateClient()
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request){
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('list'));

        $this->assertInstanceOf('Iterator', $this->applicationClient);

        foreach($this->applicationClient as $id => $application)
        {
            break;
        }

        $this->assertTrue(isset($application));
        $this->assertInstanceOf('Nexmo\Application\Application', $application);
        $this->assertSame($application->getId(), $id);

    }

    /**
     * @dataProvider getApplication
     */
    public function testGetApplication($payload, $id)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertEquals('/v2/applications/' . $id, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse());

        $application = $this->applicationClient->get($payload);

        $this->assertInstanceOf('Nexmo\Application\Application', $application);
        if($payload instanceof Application){
            $this->assertSame($payload, $application);
        }
    }

    public function getApplication()
    {
        return [
            ['1a20a124-1775-412b-b623-e6985f4aace0', '1a20a124-1775-412b-b623-e6985f4aace0'],
            [new Application('1a20a124-1775-412b-b623-e6985f4aace0'), '1a20a124-1775-412b-b623-e6985f4aace0']
        ];
    }

    /**
     * @dataProvider updateApplication
     */
    public function testUpdateApplication($payload, $method, $id, $expectedId)
    {
        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($expectedId){
            $this->assertEquals('/v2/applications/' . $expectedId, $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('PUT', $request->getMethod());

            $this->assertRequestJsonBodyContains('name', 'updated application', $request);

            // And check all other capabilities
            $capabilities = [
                'voice' => [
                    'webhooks' => [
                        'answer_url' => [
                            'address' => 'https://example.com/new_answer',
                            'http_method' => null

                        ],
                        'event_url' => [
                            'address' => 'https://example.com/new_event',
                            'http_method' => null
                        ]
                    ]
                ],
                'rtc' => [
                    'webhooks' => [
                        'event_url' => [
                            'address' => 'https://example.com/new_event',
                            'http_method' => null
                        ]
                    ]
                ],
            ];
            $this->assertRequestJsonBodyContains('capabilities', $capabilities, $request);

            return true;
        }))->willReturn($this->getResponse());

        if($id){
            $application = $this->applicationClient->$method($payload, $id);
        } else {
            $application = $this->applicationClient->$method($payload);
        }

        $this->assertInstanceOf('Nexmo\Application\Application', $application);
        if($payload instanceof Application){
            $this->assertSame($payload, $application);
        }
    }

    public function updateApplication()
    {
        $id = '1a20a124-1775-412b-b623-e6985f4aace0';
        $copy = '1a20a124-1775-412b-4444-e6985f4aace0';
        $existing = new Application($id);
        $existing->setName('updated application');
        $existing->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/new_answer');
        $existing->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/new_event');
        $existing->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/new_event');

        $new = new Application();
        $new->setName('updated application');
        $new->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/new_answer');
        $new->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/new_event');
        $new->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/new_event');

        $raw = [
            'name' => 'updated application',
            'answer_url' => 'https://example.com/new_answer',
            'event_url' => 'https://example.com/new_event'
        ];

        return [
            //can send an application to update it
            [clone $existing, 'update', null, $id],
            [clone $existing, 'put', null, $id],
            //can send raw array and id
            [$raw, 'update', $id, $id],
            [$raw, 'put', $id, $id],
            //one application overwrites another if id provided
            [clone $existing, 'update', $copy, $copy],
            [clone $existing, 'put', $copy, $copy],
            //new application replaces old if id provided
            [clone $new, 'update', $id, $id],
            [clone $new, 'put', $id, $id],

        ];
    }

    /**
     * @dataProvider deleteApplication
     */
    public function testDeleteApplication($payload, $id)
    {
        $this->nexmoClient->send(Argument::that(function(Request $request) use($id){
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
            ['abcd1234', 'abcd1234'],
        ];
    }

    /**
     * @dataProvider exceptions
     */
    public function testThrowsException($method, $response, $code)
    {
        $response = $this->getResponse($response, $code);
        $this->nexmoClient->send(Argument::type(RequestInterface::class))->willReturn($response);
        $application = new Application();

        try{
            $this->applicationClient->$method($application);
            $this->fail('did not throw exception');
        } catch (Exception $e) {
            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents(), true);
            $class = substr($code, 0, 1);

            if (!isset($data['title'])) {
                print_r($data);die;
            }
            $msg = $data['title'];
            if ($data['detail']) {
                $msg .= ': '.$data['detail'].'. See '.$data['type'].' for more information';
            }

            switch($class){
                case '4':
                    $this->assertInstanceOf('Nexmo\Client\Exception\Request', $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    $this->assertSame($application, $e->getEntity());
                    break;
                case '5':
                    $this->assertInstanceOf('Nexmo\Client\Exception\Server', $e);
                    $this->assertEquals($msg, $e->getMessage());
                    $this->assertEquals($code, $e->getCode());
                    $this->assertSame($application, $e->getEntity());
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
            ['post', 'bad', '400'],
            ['post', 'unauthorized', '401'],
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
        $this->nexmoClient->send(Argument::that(function(Request $request){
            $this->assertEquals('/v2/applications', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());

            $this->assertRequestJsonBodyContains('name', 'test application', $request);

            // Check for VBC as an object explicitly
            $request->getBody()->rewind();
            $this->assertContains('"vbc":{}', $request->getBody()->getContents());

            // And check all other capabilities
            $capabilities = [
                'voice' => [
                    'webhooks' => [
                        'answer_url' => [
                            'address' => 'https://example.com/answer',
                            'http_method' => 'GET'

                        ],
                        'event_url' => [
                            'address' => 'https://example.com/event',
                            'http_method' => 'POST'
                        ]
                    ]
                ],
                'messages' => [
                    'webhooks' => [
                        'inbound_url' => [
                            'address' => 'https://example.com/inbound',
                            'http_method' => 'POST'

                        ],
                        'status_url' => [
                            'address' => 'https://example.com/status',
                            'http_method' => 'POST'
                        ]
                    ]
                ],
                'rtc' => [
                    'webhooks' => [
                        'event_url' => [
                            'address' => 'https://example.com/event',
                            'http_method' => 'POST',
                        ],
                    ]
                ],
                'vbc' => []
            ];
            $this->assertRequestJsonBodyContains('capabilities', $capabilities, $request);

            // And the public key
            $keys = [
                'public_key' => '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n'
            ];
            $this->assertRequestJsonBodyContains('keys', $keys, $request);
            return true;
        }))->willReturn($this->getResponse('success', '201'));

        $application = $this->applicationClient->$method($payload);

        //is an application object was provided, should be the same
        $this->assertInstanceOf('Nexmo\Application\Application', $application);
        if($payload instanceof Application){
            $this->assertSame($payload, $application);
        }
    }

    public function createApplication()
    {
        $application = new Application();
        $application->setName('test application');
        $application->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/answer', 'GET');
        $application->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/event', 'POST');
        $application->getMessagesConfig()->setWebhook(MessagesConfig::STATUS, 'https://example.com/status', 'POST');
        $application->getMessagesConfig()->setWebhook(MessagesConfig::INBOUND, 'https://example.com/inbound', 'POST');
        $application->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/event', 'POST');
        $application->setPublicKey('-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n');
        $application->getVbcConfig()->enable();

        $rawV1 = [
            'name' => 'test application',
            'answer_url' => 'https://example.com/answer',
            'answer_method' => 'GET',
            'event_url' => 'https://example.com/event',
            'event_method' => 'POST',
            'status_url' => 'https://example.com/status',
            'status_method' => 'POST',
            'inbound_url' => 'https://example.com/inbound',
            'inbound_method' => 'POST',
            'vbc' => true,
            'public_key' => '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n'
        ];

        $rawV2 = [
            'name' => 'test application',
            'keys' => [
                'public_key' => '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n'
            ],
            'capabilities' => [
                'voice' => [
                    'webhooks' => [
                        'answer_url' => [
                            'address' => 'https://example.com/answer',
                            'http_method' => 'GET',
                        ],
                        'event_url' => [
                            'address' => 'https://example.com/event',
                            'http_method' => 'POST',
                        ],
                    ]
                ],
                'messages' => [
                    'webhooks' => [
                        'inbound_url' => [
                            'address' => 'https://example.com/inbound',
                            'http_method' => 'POST'

                        ],
                        'status_url' => [
                            'address' => 'https://example.com/status',
                            'http_method' => 'POST'
                        ]
                    ]
                ],
                'rtc' => [
                    'webhooks' => [
                        'event_url' => [
                            'address' => 'https://example.com/event',
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