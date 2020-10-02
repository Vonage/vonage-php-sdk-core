<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Call;

use Vonage\Call\Stream;
use VonageTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $id;

    /**
     * @var Stream
     */
    protected $entity;

    /**
     * @var Stream
     */
    protected $new;

    protected $class;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $vonageClient;

    public function setUp(): void
    {
        $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
        $this->class = Stream::class;

        $this->entity = @new Stream('3fd4d839-493e-4485-b2a5-ace527aacff3');
        $this->new = @new Stream();

        $this->vonageClient = $this->prophesize('Vonage\Client');
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->entity->setClient($this->vonageClient->reveal());
        $this->new->setClient($this->vonageClient->reveal());
    }

    public function testHasId()
    {
        $this->assertSame($this->id, $this->entity->getId());
    }

    public function testSetUrl()
    {
        $url = 'http://example.com';
        $this->entity->setUrl($url);

        $data = $this->entity->jsonSerialize();

        $this->assertSame([$url], $data['stream_url']);
    }

    public function testSetUrlArray()
    {
        $url = [
            'http://example.com',
            'http://backup.example.com'
        ];

        $this->entity->setUrl($url);
        $data = $this->entity->jsonSerialize();
        $this->assertSame($url, $data['stream_url']);
    }

    public function testSetLoop()
    {
        $loop = 10;
        $this->entity->setLoop($loop);

        $data = $this->entity->jsonSerialize();

        $this->assertSame($loop, $data['loop']);
    }

    public function testPutMakesRequest()
    {
        $this->entity->setUrl('http://example.com');
        $this->entity->setLoop(10);

        $callId = $this->id;
        $stream = $this->entity;

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $stream) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'PUT', $request);
            $expected = json_decode(json_encode($stream), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('stream', '200'));

        $event = @$this->entity->put();

        $this->assertInstanceOf('Vonage\Call\Event', $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Stream started', $event['message']);
    }

    public function testPutCanReplace()
    {
        $stream = @new Stream();
        $stream->setUrl('http://example.com');
        $stream->setLoop(10);

        $callId = $this->id;

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $stream) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'PUT', $request);
            $expected = json_decode(json_encode($stream), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('stream', '200'));

        $event = @$this->entity->put($stream);

        $this->assertInstanceOf('Vonage\Call\Event', $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Stream started', $event['message']);
    }

    public function testInvokeProxiesPutWithArgument()
    {
        $object = $this->entity;

        $this->vonageClient->send(Argument::any())->willReturn($this->getResponse('stream', '200'));
        $test = $object();
        $this->assertSame($this->entity, $test);

        $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();

        $stream = @new Stream();
        $stream->setUrl('http://example.com');

        $event = @$object($stream);

        $this->assertInstanceOf('Vonage\Call\Event', $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Stream started', $event['message']);

        $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
    }

    public function testDeleteMakesRequest()
    {
        $this->entity;
        $this->entity;

        $callId = $this->id;

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('stream-delete', '200'));

        $event = @$this->entity->delete();

        $this->assertInstanceOf('Vonage\Call\Event', $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Stream stopped', $event['message']);
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
