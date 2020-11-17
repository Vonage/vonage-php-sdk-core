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
use Vonage\Call\Call;
use Vonage\Call\Dtmf;
use Vonage\Call\Endpoint;
use Vonage\Call\Stream;
use Vonage\Call\Talk;
use Vonage\Call\Transfer;
use Vonage\Call\Webhook;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Conversations\Conversation;
use VonageTest\Psr7AssertionTrait;

use function file_get_contents;
use function fopen;
use function json_decode;
use function json_encode;

class CallTest extends TestCase
{
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

    protected $vonageClient;

    public function setUp(): void
    {
        $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
        $this->class = Call::class;

        $this->entity = @new Call('3fd4d839-493e-4485-b2a5-ace527aacff3');
        $this->new = @new Call();

        $this->vonageClient = $this->prophesize('Vonage\Client');
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        /** @noinspection PhpParamsInspection */
        $this->entity->setClient($this->vonageClient->reveal());
        /** @noinspection PhpParamsInspection */
        $this->new->setClient($this->vonageClient->reveal());
    }

    /**
     * Entities should be constructable with an ID.
     */
    public function testConstructWithId(): void
    {
        $class = $this->class;
        $entity = @new $class('3fd4d839-493e-4485-b2a5-ace527aacff3');

        $this->assertSame('3fd4d839-493e-4485-b2a5-ace527aacff3', $entity->getId());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testGetMakesRequest(): void
    {
        // @todo Remove deprecated tests
        $class = $this->class;
        $id = $this->id;
        $response = $this->getResponse('call');

        $entity = @new $class($id);
        $entity->setClient($this->vonageClient->reveal());

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($response);

        @$entity->get();

        @$this->assertEntityMatchesResponse($entity, $response);
    }

    /**
     * @param $payload
     * @param $expectedHttpCode
     * @param $expectedResponse
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     * @dataProvider putCall
     */
    public function testPutMakesRequest($payload, $expectedHttpCode, $expectedResponse): void
    {
        $id = $this->id;
        $expected = json_decode(json_encode($payload), true);

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $expected) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);

            return true;
        }))->willReturn($this->getResponse($expectedResponse, $expectedHttpCode));

        @$this->entity->put($payload);
    }

    /**
     * Can update the call with an object or a raw array.
     */
    public function putCall(): array
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
            [@new Transfer('http://example.com'), 200, 'updated'],
            [@new Transfer('http://example.com'), 204, 'empty']
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testLazyLoad(): void
    {
        // @todo Remove deprecated tests
        $id = $this->id;
        $response = $this->getResponse('call');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($response);

        $return = @$this->entity->getStatus();
        $this->assertSame('completed', $return);

        @$this->assertEntityMatchesResponse($this->entity, $response);
    }

    public function testStream(): void
    {
        // @todo Remove deprecated tests
        @$stream = $this->entity->stream;

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame($this->entity->getId(), $stream->getId());

        $this->assertSame($stream, @$this->entity->stream);
        $this->assertSame($stream, @$this->entity->stream());

        @$this->entity->stream->setUrl('http://example.com');

        $response = new Response(fopen(__DIR__ . '/responses/stream.json', 'rb'), 200);

        $id = $this->entity->getId();

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/stream', 'PUT', $request);
            return true;
        }))->willReturn($response)->shouldBeCalled();

        @$this->entity->stream($stream);
    }

    public function testSTalk(): void
    {
        // @todo Remove deprecated tests
        @$talk = $this->entity->talk;

        $this->assertInstanceOf(Talk::class, $talk);
        $this->assertSame($this->entity->getId(), $talk->getId());

        $this->assertSame($talk, @$this->entity->talk);
        $this->assertSame($talk, @$this->entity->talk());

        @$this->entity->talk->setText('Boom!');

        $response = new Response(fopen(__DIR__ . '/responses/talk.json', 'rb'), 200);

        $id = $this->entity->getId();

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/talk', 'PUT', $request);
            return true;
        }))->willReturn($response)->shouldBeCalled();

        @$this->entity->talk($talk);
    }

    public function testSDtmf(): void
    {
        // @todo Remove deprecated tests
        $dtmf = @$this->entity->dtmf;

        $this->assertInstanceOf(Dtmf::class, $dtmf);
        $this->assertSame($this->entity->getId(), $dtmf->getId());

        $this->assertSame($dtmf, @$this->entity->dtmf);
        $this->assertSame($dtmf, @$this->entity->dtmf());

        @$this->entity->dtmf->setDigits(1234);

        $response = new Response(fopen(__DIR__ . '/responses/dtmf.json', 'rb'), 200);

        $id = $this->entity->getId();

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/dtmf', 'PUT', $request);
            return true;
        }))->willReturn($response)->shouldBeCalled();

        @$this->entity->dtmf($dtmf);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testToIsSet(): void
    {
        // @todo split into discrete tests, use trait as can be useful elsewhere for consistency
        @$this->new->setTo('14845551212');
        $this->assertSame('14845551212', (string)$this->new->getTo());
        $this->assertSame('14845551212', $this->new->getTo()->getId());
        $this->assertSame('phone', $this->new->getTo()->getType());

        $data = $this->new->jsonSerialize();

        $this->assertArrayHasKey('to', $data);
        $this->assertIsArray($data['to']);
        $this->assertArrayHasKey('number', $data['to'][0]);
        $this->assertArrayHasKey('type', $data['to'][0]);
        $this->assertEquals('14845551212', $data['to'][0]['number']);
        $this->assertEquals('phone', $data['to'][0]['type']);

        $this->new->setTo(@new Endpoint('14845551212'));
        $this->assertSame('14845551212', (string)$this->new->getTo());
        $this->assertSame('14845551212', $this->new->getTo()->getId());
        $this->assertSame('phone', $this->new->getTo()->getType());

        $data = $this->new->jsonSerialize();

        $this->assertArrayHasKey('to', $data);
        $this->assertIsArray($data['to']);
        $this->assertArrayHasKey('number', $data['to'][0]);
        $this->assertArrayHasKey('type', $data['to'][0]);
        $this->assertEquals('14845551212', $data['to'][0]['number']);
        $this->assertEquals('phone', $data['to'][0]['type']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testFromIsSet(): void
    {
        @$this->new->setFrom('14845551212');
        $this->assertSame('14845551212', (string)$this->new->getFrom());
        $this->assertSame('14845551212', $this->new->getFrom()->getId());
        $this->assertSame('phone', $this->new->getFrom()->getType());

        $data = $this->new->jsonSerialize();

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('number', $data['from']);
        $this->assertArrayHasKey('type', $data['from']);
        $this->assertEquals('14845551212', $data['from']['number']);
        $this->assertEquals('phone', $data['from']['type']);

        $this->new->setFrom(@new Endpoint('14845551212'));
        $this->assertSame('14845551212', (string)$this->new->getFrom());
        $this->assertSame('14845551212', $this->new->getFrom()->getId());
        $this->assertSame('phone', $this->new->getFrom()->getType());

        $data = $this->new->jsonSerialize();

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('number', $data['from']);
        $this->assertArrayHasKey('type', $data['from']);
        $this->assertEquals('14845551212', $data['from']['number']);
        $this->assertEquals('phone', $data['from']['type']);
    }

    public function testWebhooks(): void
    {
        @$this->entity->setWebhook(Call::WEBHOOK_ANSWER, 'http://example.com');

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data[0]);
        $this->assertCount(1, $data[0]['answer_url']);
        $this->assertEquals('http://example.com', $data[0]['answer_url'][0]);

        $this->entity->setWebhook(@new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com'));

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data[0]);
        $this->assertCount(1, $data[0]['answer_url']);
        $this->assertEquals('http://example.com', $data[0]['answer_url'][0]);

        $this->entity->setWebhook(
            @new Webhook(Call::WEBHOOK_ANSWER, ['http://example.com', 'http://example.com/test'])
        );

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data[0]);
        $this->assertCount(2, $data[0]['answer_url']);
        $this->assertEquals('http://example.com', $data[0]['answer_url'][0]);
        $this->assertEquals('http://example.com/test', $data[0]['answer_url'][1]);

        $this->entity->setWebhook(@new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com', 'POST'));

        $data = $this->entity->jsonSerialize();
        $this->assertArrayHasKey('answer_method', $data[0]);
        $this->assertEquals('POST', $data[0]['answer_method']);
    }

    public function testTimers(): void
    {
        $this->entity->setTimer(Call::TIMER_LENGTH, 10);
        $data = $this->entity->jsonSerialize();

        $this->assertArrayHasKey('length_timer', $data);
        $this->assertEquals(10, $data['length_timer']);
    }

    public function testTimeouts(): void
    {
        $this->entity->setTimeout(Call::TIMEOUT_MACHINE, 10);
        $data = $this->entity->jsonSerialize();

        $this->assertArrayHasKey('machine_timeout', $data);
        $this->assertEquals(10, $data['machine_timeout']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testHydrate(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/call.json'), true);
        $this->entity->jsonUnserialize($data);

        @$this->assertEntityMatchesData($this->entity, $data);
    }

    /**
     * Use a Response object as the data source.
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function assertEntityMatchesResponse(Call $entity, Response $response): void
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
     * @param $data
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function assertEntityMatchesData(Call $entity, $data): void
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

        $this->assertInstanceOf(Conversation::class, $entity->getConversation());
        $this->assertEquals($data['conversation_uuid'], $entity->getConversation()->getId());
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
