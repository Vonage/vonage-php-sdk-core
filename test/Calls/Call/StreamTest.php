<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls\Call;

use Nexmo\Calls\Call\Stream;
use Nexmo\Calls\Collection;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;

class StreamTest extends \PHPUnit_Framework_TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $nexmoClient;

    /**
     * @var Collection
     */
    protected $collection;

    protected $callId = '1234';

    /**
     * @var Stream
     */
    protected $stream;

    public function setUp()
    {
        $this->stream = new Stream($this->callId);
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->collection = new Collection();
        $this->collection->setClient($this->nexmoClient->reveal());

        //needed until entities have access to client
        $this->stream->setCollection($this->collection);
    }

    public function testHasId()
    {
        $this->assertSame($this->callId, $this->stream->getId());
    }

    public function testSetUrl()
    {
        $url = 'http://example.com';
        $this->stream->setUrl($url);

        $data = $this->stream->jsonSerialize();

        $this->assertSame([$url], $data['stream_url']);
    }

    public function testSetUrlArray()
    {
        $url = [
            'http://example.com',
            'http://backup.example.com'
        ];
        $this->stream->setUrl($url);

        $data = $this->stream->jsonSerialize();

        $this->assertSame($url, $data['stream_url']);
    }

    public function testSetLoop()
    {
        $loop = 10;
        $this->stream->setLoop($loop);

        $data = $this->stream->jsonSerialize();

        $this->assertSame($loop, $data['loop']);
    }

    public function testPutMakesRequest()
    {
        $this->stream->setUrl('http://example.com');
        $this->stream->setLoop(10);

        $callId = $this->callId;
        $stream = $this->stream;

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($callId, $stream){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'PUT', $request);
            $expected = json_decode(json_encode($stream), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('stream', '200'));

        $this->stream->put();
    }

    public function testPutCanReplace()
    {
        $stream = new Stream();
        $stream->setUrl('http://example.com');
        $stream->setLoop(10);

        $callId = $this->callId;

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($callId, $stream){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'PUT', $request);
            $expected = json_decode(json_encode($stream), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('stream', '200'));

        $this->stream->put($stream);
    }

    public function testDeleteMakesRequest()
    {
        $this->stream;
        $this->stream;

        $callId = $this->callId;

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($callId){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('stream-delete', '204'));

        $this->stream->delete();
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
