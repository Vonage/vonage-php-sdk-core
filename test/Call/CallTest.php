<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Call\Call;
use Nexmo\Call\Endpoint;
use Nexmo\Call\Transfer;
use Nexmo\Call\Webhook;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use EnricoStahn\JsonAssert\Assert as JsonAssert;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class CallTest extends TestCase
{
    use JsonAssert;
    use Psr7AssertionTrait;

    /**
     * @var Call
     */
    protected $entity;

    /**
     * @var Call
     */
    protected $new;

    protected $class;

    protected $id;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $nexmoClient;

    public function setUp()
    {
        $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
        $this->class = Call::class;

        $this->entity = new Call('3fd4d839-493e-4485-b2a5-ace527aacff3');
        $this->new = new Call();

        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->entity->setClient($this->nexmoClient->reveal());
        $this->new->setClient($this->nexmoClient->reveal());
    }

    /**
     * Entities should be constructable with an ID.
     */
    public function testConstructWithId()
    {
        $class = $this->class;
        $entity = new $class('3fd4d839-493e-4485-b2a5-ace527aacff3');
        $this->assertSame('3fd4d839-493e-4485-b2a5-ace527aacff3', $entity->getId());
    }

    /**
     * get() should explicitly fetch the data.
     */
    public function testGetMakesRequest()
    {
        $class = $this->class;
        $id = $this->id;
        $response = $this->getResponse('call');

        $entity = new $class($id);
        $entity->setClient($this->nexmoClient->reveal());

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($response);

        $entity->get();

        $this->assertEntityMatchesResponse($entity, $response);
    }

    /**
     * @param $payload
     * @dataProvider putCall
     */
    public function testPutMakesRequest($payload, $expectedHttpCode, $expectedResponse)
    {
        $id = $this->id;
        $expected = json_decode(json_encode($payload), true);

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id, $expected){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);

            return true;
        }))->willReturn($this->getResponse($expectedResponse, $expectedHttpCode));

        $this->entity->put($payload);
    }

    /**
     * Can update the call with an object or a raw array.
     * @return array
     */
    public function putCall()
    {
        $transfer = [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => ['http://example.com']
            ]
        ];

        return [
            [$transfer, 200, 'updated'],
            [new Transfer('http://example.com'), 200, 'updated'],
            [new Transfer('http://example.com'), 204, 'empty']
        ];
    }

    public function testLazyLoad()
    {
        $id = $this->id;
        $response = $this->getResponse('call');

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($response);

        $return = $this->entity->getStatus();
        $this->assertSame('completed', $return);

        $this->assertEntityMatchesResponse($this->entity, $response);
    }

    public function testStream()
    {
        $stream = $this->entity->stream;

        $this->assertInstanceOf('Nexmo\Call\Stream', $stream);
        $this->assertSame($this->entity->getId(), $stream->getId());

        $this->assertSame($stream, $this->entity->stream);
        $this->assertSame($stream, $this->entity->stream());

        $this->entity->stream->setUrl('http://example.com');

        $response = new Response(fopen(__DIR__ . '/responses/stream.json', 'r'), 200);

        $id = $this->entity->getId();

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/stream', 'PUT', $request);
            return true;
        }))->willReturn($response)->shouldBeCalled();

        $this->entity->stream($stream);
    }

    public function testSTalk()
    {
        $talk = $this->entity->talk;

        $this->assertInstanceOf('Nexmo\Call\Talk', $talk);
        $this->assertSame($this->entity->getId(), $talk->getId());

        $this->assertSame($talk, $this->entity->talk);
        $this->assertSame($talk, $this->entity->talk());

        $this->entity->talk->setText('Boom!');

        $response = new Response(fopen(__DIR__ . '/responses/talk.json', 'r'), 200);

        $id = $this->entity->getId();

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/talk', 'PUT', $request);
            return true;
        }))->willReturn($response)->shouldBeCalled();

        $this->entity->talk($talk);
    }

    public function testSDtmf()
    {
        $dtmf = $this->entity->dtmf;

        $this->assertInstanceOf('Nexmo\Call\Dtmf', $dtmf);
        $this->assertSame($this->entity->getId(), $dtmf->getId());

        $this->assertSame($dtmf, $this->entity->dtmf);
        $this->assertSame($dtmf, $this->entity->dtmf());

        $this->entity->dtmf->setDigits(1234);

        $response = new Response(fopen(__DIR__ . '/responses/dtmf.json', 'r'), 200);

        $id = $this->entity->getId();

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/dtmf', 'PUT', $request);
            return true;
        }))->willReturn($response)->shouldBeCalled();

        $this->entity->dtmf($dtmf);
    }

    //split into discrete tests, use trait as can be useful elsewhere for consistency
    public function testToIsSet()
    {
        $this->new->setTo('14845551212');
        $this->assertSame('14845551212', (string) $this->new->getTo());
        $this->assertSame('14845551212', $this->new->getTo()->getId());
        $this->assertSame('phone', $this->new->getTo()->getType());

        $data = $this->new->jsonSerialize();

        $this->assertArrayHasKey('to', $data);
        $this->assertInternalType('array', $data['to']);
        $this->assertArrayHasKey('number', $data['to'][0]);
        $this->assertArrayHasKey('type', $data['to'][0]);
        $this->assertEquals('14845551212', $data['to'][0]['number']);
        $this->assertEquals('phone', $data['to'][0]['type']);

        $this->new->setTo(new Endpoint('14845551212'));
        $this->assertSame('14845551212', (string) $this->new->getTo());
        $this->assertSame('14845551212', $this->new->getTo()->getId());
        $this->assertSame('phone', $this->new->getTo()->getType());

        $data = $this->new->jsonSerialize();

        $this->assertArrayHasKey('to', $data);
        $this->assertInternalType('array', $data['to']);
        $this->assertArrayHasKey('number', $data['to'][0]);
        $this->assertArrayHasKey('type', $data['to'][0]);
        $this->assertEquals('14845551212', $data['to'][0]['number']);
        $this->assertEquals('phone', $data['to'][0]['type']);
    }

    public function testFromIsSet()
    {
        $this->new->setFrom('14845551212');
        $this->assertSame('14845551212', (string) $this->new->getFrom());
        $this->assertSame('14845551212', $this->new->getFrom()->getId());
        $this->assertSame('phone', $this->new->getFrom()->getType());

        $data = $this->new->jsonSerialize();

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('number', $data['from']);
        $this->assertArrayHasKey('type', $data['from']);
        $this->assertEquals('14845551212', $data['from']['number']);
        $this->assertEquals('phone', $data['from']['type']);

        $this->new->setFrom(new Endpoint('14845551212'));
        $this->assertSame('14845551212', (string) $this->new->getFrom());
        $this->assertSame('14845551212', $this->new->getFrom()->getId());
        $this->assertSame('phone', $this->new->getFrom()->getType());

        $data = $this->new->jsonSerialize();

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('number', $data['from']);
        $this->assertArrayHasKey('type', $data['from']);
        $this->assertEquals('14845551212', $data['from']['number']);
        $this->assertEquals('phone', $data['from']['type']);
    }

    public function testWebhooks()
    {
        $this->entity->setWebhook(Call::WEBHOOK_ANSWER, 'http://example.com');

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data);
        $this->assertCount(1, $data['answer_url']);
        $this->assertEquals('http://example.com', $data['answer_url'][0]);

        $this->entity->setWebhook(new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com'));

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data);
        $this->assertCount(1, $data['answer_url']);
        $this->assertEquals('http://example.com', $data['answer_url'][0]);

        $this->entity->setWebhook(new Webhook(Call::WEBHOOK_ANSWER, ['http://example.com', 'http://example.com/test']));

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data);
        $this->assertCount(2, $data['answer_url']);
        $this->assertEquals('http://example.com', $data['answer_url'][0]);
        $this->assertEquals('http://example.com/test', $data['answer_url'][1]);

        $this->entity->setWebhook(new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com', 'POST'));

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('answer_method', $data);
        $this->assertEquals('POST', $data['answer_method']);
    }

    public function testTimers()
    {
        $this->entity->setTimer(Call::TIMER_LENGTH, 10);

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('length_timer', $data);
        $this->assertEquals(10, $data['length_timer']);
    }

    public function testTimeouts()
    {
        $this->entity->setTimeout(Call::TIMEOUT_MACHINE, 10);

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('machine_timeout', $data);
        $this->assertEquals(10, $data['machine_timeout']);
    }

    public function testHydrate()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/call.json'), true);
        $this->entity->jsonUnserialize($data);

        $this->assertEntityMatchesData($this->entity, $data);
    }

    /**
     * Use a Response object as the data source.
     *
     * @param Call $entity
     * @param Response $response
     */
    public function assertEntityMatchesResponse(Call $entity, Response $response)
    {
        $response->getBody()->rewind();
        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        $this->assertEntityMatchesData($entity, $data);
    }

    /**
     * Assert that the given response data is accessible via the object. This is the real work done by the hydration
     * test; however, it's also needed to test that API calls - $entity->get(), $entity->post() - actually set the
     * response data without coupling to the internal methods.
     *
     * @param Call $entity
     * @param $data
     */
    public function assertEntityMatchesData(Call $entity, $data)
    {
        $this->assertSame($data['uuid'], $entity->getId());

        $this->assertEquals($data['to']['type'], $entity->getTo()->getType());
        $this->assertEquals($data['from']['type'], $entity->getFrom()->getType());

        $this->assertEquals($data['to']['number'], $entity->getTo()->getId());
        $this->assertEquals($data['from']['number'], $entity->getFrom()->getId());

        $this->assertEquals($data['to']['number'], $entity->getTo()->getNumber());
        $this->assertEquals($data['from']['number'], $entity->getFrom()->getNumber());

        $this->assertEquals($data['status'], $entity->getStatus());
        $this->assertEquals($data['direction'], $entity->getDirection());

        $this->assertInstanceOf('Nexmo\Conversations\Conversation', $entity->getConversation());
        $this->assertEquals($data['conversation_uuid'], $entity->getConversation()->getId());
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
