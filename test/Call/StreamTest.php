<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Call;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Call\Event;
use Vonage\Call\Stream;
use Vonage\Client;
use VonageTest\Psr7AssertionTrait;

use function fopen;
use function json_decode;
use function json_encode;

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

    protected $vonageClient;

    public function setUp(): void
    {
        $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
        $this->class = Stream::class;

        $this->entity = @new Stream('3fd4d839-493e-4485-b2a5-ace527aacff3');
        $this->new = @new Stream();

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        /** @noinspection PhpParamsInspection */
        $this->entity->setClient($this->vonageClient->reveal());

        /** @noinspection PhpParamsInspection */
        $this->new->setClient($this->vonageClient->reveal());
    }

    public function testHasId(): void
    {
        $this->assertSame($this->id, $this->entity->getId());
    }

    public function testSetUrl(): void
    {
        $url = 'http://example.com';
        $this->entity->setUrl($url);
        $data = $this->entity->jsonSerialize();

        $this->assertSame([$url], $data['stream_url']);
    }

    public function testSetUrlArray(): void
    {
        $url = ['http://example.com', 'http://backup.example.com'];
        $this->entity->setUrl($url);
        $data = $this->entity->jsonSerialize();

        $this->assertSame($url, $data['stream_url']);
    }

    public function testSetLoop(): void
    {
        $loop = 10;
        $this->entity->setLoop($loop);
        $data = $this->entity->jsonSerialize();

        $this->assertSame($loop, $data['loop']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws Client\Exception\Server
     */
    public function testPutMakesRequest(): void
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
        }))->willReturn($this->getResponse('stream', 200));

        $event = @$this->entity->put();

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Stream started', $event['message']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws Client\Exception\Server
     */
    public function testPutCanReplace(): void
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
        }))->willReturn($this->getResponse('stream', 200));

        $event = @$this->entity->put($stream);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Stream started', $event['message']);
    }

    /**
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws Client\Exception\Server
     * @throws ClientExceptionInterface
     */
    public function testInvokeProxiesPutWithArgument(): void
    {
        $object = $this->entity;

        $this->vonageClient->send(Argument::any())->willReturn($this->getResponse('stream', 200));
        $test = $object();

        $this->assertSame($this->entity, $test);

        $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();

        $stream = @new Stream();
        $stream->setUrl('http://example.com');

        $event = @$object($stream);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Stream started', $event['message']);

        $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws Client\Exception\Server
     */
    public function testDeleteMakesRequest(): void
    {
        $this->entity;
        $this->entity;

        $callId = $this->id;

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/stream', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('stream-delete', 200));

        $event = @$this->entity->delete();

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Stream stopped', $event['message']);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
