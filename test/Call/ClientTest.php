<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Call\Call;
use Nexmo\Call\Client;
use Nexmo\Call\Filter;
use Prophecy\Argument;
use Nexmo\Call\Endpoint;
use Nexmo\Call\Hydrator;
use Nexmo\Client\Exception;
use Nexmo\Entity\Collection;
use Zend\Diactoros\Response;
use Nexmo\Call\NCCO\Transfer;
use PHPUnit\Framework\TestCase;
use Nexmo\Client\OpenAPIResource;
use NexmoTest\Psr7AssertionTrait;
use Psr\Http\Message\RequestInterface;
use Nexmo\Conversations\Client as ConversationsClient;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $nexmoClient;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $openAPI;

    /**
     * @var Client
     */
    protected $collection;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->openAPI = new OpenAPIResource();
        $this->openAPI->setClient($this->nexmoClient->reveal());
        $this->openAPI->setBaseUri('/v1/calls');
        $this->openAPI->setCollectionName('calls');

        // Just need this to satisfy the hydrator, nothing in this test suite should actually call it
        $conversationClient = $this->prophesize(ConversationsClient::class);
        $this->client = new Client($this->openAPI, new Hydrator($conversationClient->reveal()));
        $this->client->setClient($this->nexmoClient->reveal());
    }

    public function testSearchWithFilter()
    {
        $this->markTestSkipped('Need to get a proper response to wire this up');
        $filter = new Filter();
        $return = $this->client->search($filter);

        $this->assertInstanceOf(Collection::class, $return);
        $this->assertSame($return->getFilter(), $filter);
    }

    /**
     * Using `get()` should fetch the call data. Will accept both a string id and an object. Must return the same object
     * if that's the input.
     *
     * @dataProvider getCall
     */
    public function testGetIsNotLazy($payload, $id)
    {
        //this generally proxies the call resource, but we're testing the correct request, not the proxy
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('call'))->shouldBeCalled();

        $call = $this->client->get($payload);

        $this->assertInstanceOf('Nexmo\Call\Call', $call);
        if ($payload instanceof Call) {
            $this->assertSame($payload, $call);
        }
    }

    /**
     * @dataProvider postCall
     */
    public function testCreatePostCall($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('created', '201'));

        $call = $this->client->$method($payload);

        $this->assertInstanceOf('Nexmo\Call\Call', $call);
        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $call->getId());
    }

    /**
     * @dataProvider postCallNcco
     */
    public function testCreatePostCallNcco($payload)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $ncco = [['action' => 'talk', 'text' => 'Hello World']];

            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            $this->assertRequestJsonBodyContains('ncco', $ncco, $request);
            return true;
        }))->willReturn($this->getResponse('created', '201'));

        $call = $this->client->post($payload);

        $this->assertInstanceOf('Nexmo\Call\Call', $call);
        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $call->getId());
    }
    
    /**
     * @dataProvider postCall
     */
    public function testCreatePostCallErrorFromVApi($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_vapi', '400'));

        try {
            $call = $this->client->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), 'Bad Request');
        }
    }

    /**
     * @dataProvider postCall
     */
    public function testCreatePostCallErrorFromProxy($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_proxy', '400'));

        try {
            $call = $this->client->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), 'Unsupported Media Type');
        }
    }

    /**
     * @dataProvider postCall
     */
    public function testCreatePostCallErrorUnknownFormat($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_unknown_format', '400'));

        try {
            $call = $this->client->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), "Unexpected error");
        }
    }

    /**
     * Update an existing call with an NCCO
     * @dataProvider putCall
     */
    public function testPutCall($expectedId, $call, $ncco)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($expectedId, $ncco) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $expectedId, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($ncco->toArray()), $request);
            return true;
        }))->willReturn($this->getResponse('updated'))->shouldBeCalled();

        $this->client->put($call, $ncco);
    }

    /**
     * Test that sending a DMTF sends the correct body to the correct URI
     */
    public function testSendDTMFToCall()
    {
        $callID = '63f61863-4a51-4f6b-86e1-46edebcf9356';
        $digits = '121345';

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($callID, $digits) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callID . '/dtmf', 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode(['digits' => $digits]), $request);
            return true;
        }))->willReturn($this->getResponse('dtmf'))->shouldBeCalled();

        $call = new Call($callID);
        $this->client->dtmf($call, $digits);
    }

    /**
     * Test that stopping an audio stream sends the correct request to the correct URI
     */
    public function testStopStreamingAudioInCall()
    {
        $callID = '63f61863-4a51-4f6b-86e1-46edebcf9356';

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($callID) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callID . '/stream', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('stream-delete'))->shouldBeCalled();

        $call = new Call($callID);
        $this->client->streamAudioStop($call);
    }

    /**
     * Test that sending an audio stream sends the correct body to the correct URI
     * @dataProvider audioForCalls
     */
    public function testStreamAudioIntoCall($call, $url, $loop, $level)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($call, $url, $loop, $level) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $call->getID() . '/stream', 'PUT', $request);
            $expectedData = [
                'stream_url' => $url,
                'loop' => $loop,
                'level' => $level
            ];
            $this->assertRequestBodyIsJson(json_encode($expectedData), $request);
            return true;
        }))->willReturn($this->getResponse('stream'))->shouldBeCalled();

        $this->client->streamAudio($call, $url, $loop, $level);
    }

    /**
     * Test that staopping a TTS sends the correct request to the correct URI
     */
    public function testStopTTSInCall()
    {
        $callID = '63f61863-4a51-4f6b-86e1-46edebcf9356';

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($callID) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callID . '/talk', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('talk-delete'))->shouldBeCalled();

        $call = new Call($callID);
        $this->client->talkStop($call);
    }

    /**
     * Test that sending a TTS sends the correct body to the correct URI
     * @dataProvider ttsForCalls
     */
    public function testPlayTTSIntoCall($call, $text, $voice, $loop, $level)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($call, $text, $voice, $loop, $level) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $call->getID() . '/talk', 'PUT', $request);
            $expectedData = [
                'text' => $text,
                'voice_name' => $voice,
                'loop' => $loop,
                'level' => $level
            ];
            $this->assertRequestBodyIsJson(json_encode($expectedData), $request);
            return true;
        }))->willReturn($this->getResponse('talk'))->shouldBeCalled();

        $this->client->talk($call, $text, $voice, $loop, $level);
    }

    public function ttsForCalls()
    {
        return [
            [new Call('123-4561'), 'This is text', 'Kimberly', 1, -0.1],
            [new Call('123-4562'), 'This is text2', 'Salli', 10, -0.2],
            [new Call('123-4563'), 'This is text3', 'Dora', 2, .5],
            [new Call('123-4564'), 'This is text4', 'Ricardo', 4, 0],
        ];
    }

    public function audioForCalls()
    {
        return [
            [new Call('123-4561'), ['https://example.com/audio.mp3'], 1, -0.1],
            [new Call('123-4562'), ['https://example.com/audio.mp3', 'https://example.com/audio2.mp3'], 10, -0.2],
            [new Call('123-4563'), ['https://example.com/audio6.mp3'], 2, .5],
            [new Call('123-4564'), ['https://example.com/audio4.mp3'], 4, 0],
        ];
    }

    /**
     * Getting a call can use an object or an ID.
     *
     * @return array
     */
    public function getCall()
    {
        return [
            ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
        ];
    }

    /**
     * Creating a call with an NCCO can take a Call object or a simple array.
     * @return array
     */
    public function postCallNcco()
    {
        $raw = [
            'to' => [[
                'type' => 'phone',
                'number' => '14843331234'
            ]],
            'from' => [
                'type' => 'phone',
                'number' => '14843335555'
            ],
            'ncco' => [
                [
                    'action' => 'talk',
                    'text' => 'Hello World'
                ]
            ]
        ];


        $call = new Call();
        $call->setTo(new Endpoint('14843331234'))
             ->setFrom(new Endpoint('14843335555'))
             ->setNcco([
                 [
                     'action' => 'talk',
                     'text' => 'Hello World'
                 ]
             ]);

        return [
            'object' => [clone $call],
        ];
    }
    /**
     * Creating a call can take a Call object or a simple array.
     * @return array
     */
    public function postCall()
    {
        $raw = [
            'to' => [[
                'type' => 'phone',
                'number' => '14843331234'
            ]],
            'from' => [
                'type' => 'phone',
                'number' => '14843335555'
            ],
            'answer_url' => ['https://example.com/answer'],
            'event_url' => ['https://example.com/event'],
            'answer_method' => 'POST',
            'event_method' => 'POST'
        ];


        $call = new Call();
        $call->setTo(new Endpoint('14843331234'))
             ->setFrom(new Endpoint('14843335555'))
             ->setWebhook(Call::WEBHOOK_ANSWER, 'https://example.com/answer', 'POST')
             ->setWebhook(Call::WEBHOOK_EVENT, 'https://example.com/event', 'POST');

        return [
            [clone $call, 'create'],
            [clone $call, 'post'],
        ];
    }

    /**
     * Can update the call with a NCCO
     * @return array
     */
    public function putCall()
    {
        $id = '1234abcd';
        $call = new Call($id);
        $transfer = new Transfer('http://example.com');

        return [
            [$id, $call, $transfer],
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
