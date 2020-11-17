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
use Vonage\Call\Talk;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use VonageTest\Psr7AssertionTrait;

use function fopen;
use function json_decode;
use function json_encode;

class TalkTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $id;

    /**
     * @var Talk
     */
    protected $entity;

    /**
     * @var Talk
     */
    protected $new;

    protected $class;

    protected $vonageClient;

    public function setUp(): void
    {
        $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
        $this->class = Talk::class;

        $this->entity = @new Talk('3fd4d839-493e-4485-b2a5-ace527aacff3');
        $this->new = @new Talk();

        $this->vonageClient = $this->prophesize('Vonage\Client');
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

    /**
     * @param $value
     * @param $param
     * @param $setter
     * @param $expected
     * @dataProvider setterParameters
     */
    public function testSetParams($value, $param, $setter, $expected): void
    {
        $this->entity->$setter($value);
        $data = $this->entity->jsonSerialize();

        $this->assertEquals($expected, $data[$param]);
    }

    /**
     * @param $value
     * @param $param
     * @dataProvider setterParameters
     */
    public function testArrayParams($value, $param): void
    {
        $this->entity[$param] = $value;
        $data = $this->entity->jsonSerialize();

        $this->assertEquals($value, $data[$param]);
    }

    public function setterParameters(): array
    {
        return [
            ['something I want to say', 'text', 'setText', 'something I want to say'],
            ['Ivy', 'voice_name', 'setVoiceName', 'Ivy'],
            [0, 'loop', 'setLoop', '0'],
            [1, 'loop', 'setLoop', '1'],
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testPutMakesRequest(): void
    {
        $this->entity->setText('Bingo!');

        $callId = $this->id;
        $entity = $this->entity;

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $entity) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/talk', 'PUT', $request);
            $expected = json_decode(json_encode($entity), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('talk', 200));

        $event = @$this->entity->put();

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Talk started', $event['message']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testPutCanReplace(): void
    {
        $class = $this->class;

        $entity = @new $class();
        $entity->setText('Ding!');

        $callId = $this->id;

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $entity) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/talk', 'PUT', $request);
            $expected = json_decode(json_encode($entity), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('talk', 200));

        $event = @$this->entity->put($entity);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Talk started', $event['message']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testInvokeProxiesPutWithArgument(): void
    {
        $object = $this->entity;

        $this->vonageClient->send(Argument::any())->willReturn($this->getResponse('talk', 200));
        $test = $object();
        $this->assertSame($this->entity, $test);

        $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();

        $class = $this->class;
        $entity = @new $class();
        $entity->setText('Hello!');

        $event = @$object($entity);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Talk started', $event['message']);

        $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testDeleteMakesRequest(): void
    {
        $callId = $this->id;

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/talk', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('talk-delete', 200));

        $event = @$this->entity->delete();

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('Talk stopped', $event['message']);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
