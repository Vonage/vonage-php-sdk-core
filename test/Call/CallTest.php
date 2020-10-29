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

    /**
     * @var mixed
     */
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

        self::assertSame('3fd4d839-493e-4485-b2a5-ace527aacff3', $entity->getId());
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
            self::assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($response);

        @$entity->get();

        @$this->assertEntityMatchesResponse($entity, $response);
    }

    /**
     * @param $payload
     * @param $expectedHttpCode
     * @param $expectedResponse
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
            self::assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            self::assertEquals($expected, $body);

            return true;
        }))->willReturn($this->getResponse($expectedResponse, $expectedHttpCode));

        @$this->entity->put($payload);
    }

    /**
     * Can update the call with an object or a raw array.
     *
     * @return array
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
            self::assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($response);

        $return = @$this->entity->getStatus();
        self::assertSame('completed', $return);

        @$this->assertEntityMatchesResponse($this->entity, $response);
    }

    public function testStream(): void
    {
        // @todo Remove deprecated tests
        @$stream = $this->entity->stream;

        self::assertInstanceOf(Stream::class, $stream);
        self::assertSame($this->entity->getId(), $stream->getId());

        self::assertSame($stream, @$this->entity->stream);
        self::assertSame($stream, @$this->entity->stream());

        @$this->entity->stream->setUrl('http://example.com');

        $response = new Response(fopen(__DIR__ . '/responses/stream.json', 'rb'), 200);

        $id = $this->entity->getId();

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            self::assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/stream', 'PUT', $request);
            return true;
        }))->willReturn($response)->shouldBeCalled();

        @$this->entity->stream($stream);
    }

    public function testSTalk(): void
    {
        // @todo Remove deprecated tests
        @$talk = $this->entity->talk;

        self::assertInstanceOf(Talk::class, $talk);
        self::assertSame($this->entity->getId(), $talk->getId());

        self::assertSame($talk, @$this->entity->talk);
        self::assertSame($talk, @$this->entity->talk());

        @$this->entity->talk->setText('Boom!');

        $response = new Response(fopen(__DIR__ . '/responses/talk.json', 'rb'), 200);

        $id = $this->entity->getId();

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            self::assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/talk', 'PUT', $request);
            return true;
        }))->willReturn($response)->shouldBeCalled();

        @$this->entity->talk($talk);
    }

    public function testSDtmf(): void
    {
        // @todo Remove deprecated tests
        $dtmf = @$this->entity->dtmf;

        self::assertInstanceOf(Dtmf::class, $dtmf);
        self::assertSame($this->entity->getId(), $dtmf->getId());

        self::assertSame($dtmf, @$this->entity->dtmf);
        self::assertSame($dtmf, @$this->entity->dtmf());

        @$this->entity->dtmf->setDigits(1234);

        $response = new Response(fopen(__DIR__ . '/responses/dtmf.json', 'rb'), 200);

        $id = $this->entity->getId();

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            self::assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/dtmf', 'PUT', $request);
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
        self::assertSame('14845551212', (string)$this->new->getTo());
        self::assertSame('14845551212', $this->new->getTo()->getId());
        self::assertSame('phone', $this->new->getTo()->getType());

        $data = $this->new->jsonSerialize();

        self::assertArrayHasKey('to', $data);
        self::assertIsArray($data['to']);
        self::assertArrayHasKey('number', $data['to'][0]);
        self::assertArrayHasKey('type', $data['to'][0]);
        self::assertEquals('14845551212', $data['to'][0]['number']);
        self::assertEquals('phone', $data['to'][0]['type']);

        $this->new->setTo(@new Endpoint('14845551212'));
        self::assertSame('14845551212', (string)$this->new->getTo());
        self::assertSame('14845551212', $this->new->getTo()->getId());
        self::assertSame('phone', $this->new->getTo()->getType());

        $data = $this->new->jsonSerialize();

        self::assertArrayHasKey('to', $data);
        self::assertIsArray($data['to']);
        self::assertArrayHasKey('number', $data['to'][0]);
        self::assertArrayHasKey('type', $data['to'][0]);
        self::assertEquals('14845551212', $data['to'][0]['number']);
        self::assertEquals('phone', $data['to'][0]['type']);
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
        self::assertSame('14845551212', (string)$this->new->getFrom());
        self::assertSame('14845551212', $this->new->getFrom()->getId());
        self::assertSame('phone', $this->new->getFrom()->getType());

        $data = $this->new->jsonSerialize();

        self::assertArrayHasKey('from', $data);
        self::assertArrayHasKey('number', $data['from']);
        self::assertArrayHasKey('type', $data['from']);
        self::assertEquals('14845551212', $data['from']['number']);
        self::assertEquals('phone', $data['from']['type']);

        $this->new->setFrom(@new Endpoint('14845551212'));
        self::assertSame('14845551212', (string)$this->new->getFrom());
        self::assertSame('14845551212', $this->new->getFrom()->getId());
        self::assertSame('phone', $this->new->getFrom()->getType());

        $data = $this->new->jsonSerialize();

        self::assertArrayHasKey('from', $data);
        self::assertArrayHasKey('number', $data['from']);
        self::assertArrayHasKey('type', $data['from']);
        self::assertEquals('14845551212', $data['from']['number']);
        self::assertEquals('phone', $data['from']['type']);
    }

    public function testWebhooks(): void
    {
        @$this->entity->setWebhook(Call::WEBHOOK_ANSWER, 'http://example.com');

        $data = $this->entity->jsonSerialize();
        self::assertArrayHasKey('answer_url', $data[0]);
        self::assertCount(1, $data[0]['answer_url']);
        self::assertEquals('http://example.com', $data[0]['answer_url'][0]);

        $this->entity->setWebhook(@new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com'));

        $data = $this->entity->jsonSerialize();
        self::assertArrayHasKey('answer_url', $data[0]);
        self::assertCount(1, $data[0]['answer_url']);
        self::assertEquals('http://example.com', $data[0]['answer_url'][0]);

        $this->entity->setWebhook(
            @new Webhook(Call::WEBHOOK_ANSWER, ['http://example.com', 'http://example.com/test'])
        );

        $data = $this->entity->jsonSerialize();
        self::assertArrayHasKey('answer_url', $data[0]);
        self::assertCount(2, $data[0]['answer_url']);
        self::assertEquals('http://example.com', $data[0]['answer_url'][0]);
        self::assertEquals('http://example.com/test', $data[0]['answer_url'][1]);

        $this->entity->setWebhook(@new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com', 'POST'));

        $data = $this->entity->jsonSerialize();
        self::assertArrayHasKey('answer_method', $data[0]);
        self::assertEquals('POST', $data[0]['answer_method']);
    }

    public function testTimers(): void
    {
        $this->entity->setTimer(Call::TIMER_LENGTH, 10);
        $data = $this->entity->jsonSerialize();

        self::assertArrayHasKey('length_timer', $data);
        self::assertEquals(10, $data['length_timer']);
    }

    public function testTimeouts(): void
    {
        $this->entity->setTimeout(Call::TIMEOUT_MACHINE, 10);
        $data = $this->entity->jsonSerialize();

        self::assertArrayHasKey('machine_timeout', $data);
        self::assertEquals(10, $data['machine_timeout']);
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
     * @param Call $entity
     * @param Response $response
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
     * @param Call $entity
     * @param $data
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function assertEntityMatchesData(Call $entity, $data): void
    {
        self::assertSame($data['uuid'], $entity->getId());

        self::assertEquals($data['to']['type'], $entity->getTo()->getType());
        self::assertEquals($data['from']['type'], $entity->getFrom()->getType());

        self::assertEquals($data['to']['number'], $entity->getTo()->getId());
        self::assertEquals($data['from']['number'], $entity->getFrom()->getId());

        self::assertEquals($data['to']['number'], $entity->getTo()->getNumber());
        self::assertEquals($data['from']['number'], $entity->getFrom()->getNumber());

        self::assertEquals($data['status'], $entity->getStatus());
        self::assertEquals($data['direction'], $entity->getDirection());

        self::assertInstanceOf(Conversation::class, $entity->getConversation());
        self::assertEquals($data['conversation_uuid'], $entity->getConversation()->getId());
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @param int $status
     * @return Response
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
