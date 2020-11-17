<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Conversation;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\Exception as ClientException;
use Vonage\Conversations\Collection;
use Vonage\Conversations\Conversation;
use VonageTest\Psr7AssertionTrait;

use function fopen;
use function json_encode;

class CollectionTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $vonageClient;

    /**
     * @var Collection
     */
    protected $collection;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->collection = new Collection();
        /** @noinspection PhpParamsInspection */
        $this->collection->setClient($this->vonageClient->reveal());
    }

    /**
     * Getting an entity from the collection should not fetch it if we use the array interface.
     *
     * @dataProvider getConversation
     *
     * @param $payload
     * @param $id
     */
    public function testArrayIsLazy($payload, $id): void
    {
        $this->vonageClient->send(Argument::any())->willReturn($this->getResponse('conversation'));

        $conversation = $this->collection[$payload];
        $this->assertInstanceOf(Conversation::class, $conversation);

        $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();
        $this->assertEquals($id, $conversation->getId());

        if ($payload instanceof Conversation) {
            $this->assertSame($payload, $conversation);
        }

        // Once we call get() the rest of the data should be populated
        $conversation->get();
        $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * Using `get()` should fetch the conversation data. Will accept both a string id and an object.
     * Must return the same object if that's the input.
     *
     * @dataProvider getConversation
     *
     * @param $payload
     * @param $id
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function testGetIsNotLazy($payload, $id): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/beta/conversations/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('conversation'))->shouldBeCalled();

        $conversation = $this->collection->get($payload);

        if ($payload instanceof Conversation) {
            $this->assertSame($payload, $conversation);
        }
    }

    /**
     * @dataProvider postConversation
     *
     * @param $payload
     * @param $method
     */
    public function testCreatePostConversation($payload, $method): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/beta/conversations', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);

            return true;
        }))->willReturn($this->getResponse('conversation', 200));

        $conversation = $this->collection->$method($payload);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('CON-aaaaaaaa-bbbb-cccc-dddd-0123456789ab', $conversation->getId());
    }

    /**
     * @dataProvider postConversation
     *
     * @param $payload
     * @param $method
     */
    public function testCreatePostConversationErrorFromVApi($payload, $method): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/beta/conversations', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);

            return true;
        }))->willReturn($this->getResponse('error_stitch', 400));

        try {
            $this->collection->$method($payload);

            self::fail('Expected to throw request exception');
        } catch (ClientException\Request $e) {
            $this->assertEquals('the token was rejected', $e->getMessage());
        }
    }

    /**
     * @dataProvider postConversation
     *
     * @param $payload
     * @param $method
     */
    public function testCreatePostCallErrorFromProxy($payload, $method): void
    {
        self::markTestSkipped();

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/conversation', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);

            return true;
        }))->willReturn($this->getResponse('error_proxy', 400));

        try {
            $this->collection->$method($payload);

            self::fail('Expected to throw request exception');
        } catch (ClientException\Request $e) {
            $this->assertEquals('Unsupported Media Type', $e->getMessage());
        }
    }

    /**
     * @dataProvider postConversation
     *
     * @param $payload
     * @param $method
     */
    public function testCreatePostCallErrorUnknownFormat($payload, $method): void
    {
        self::markTestSkipped();

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/conversation', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);

            return true;
        }))->willReturn($this->getResponse('error_unknown_format', 400));

        try {
            $this->collection->$method($payload);

            self::fail('Expected to throw request exception');
        } catch (ClientException\Request $e) {
            $this->assertEquals("Unexpected error", $e->getMessage());
        }
    }

    /**
     * Getting a conversation can use an object or an ID.
     */
    public function getConversation(): array
    {
        return [
            ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
            [new Conversation('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
        ];
    }

    /**
     * Creating a conversation can take a Call object or a simple array.
     */
    public function postConversation(): array
    {
        $raw = [
            'name' => 'demo',
            'display_name' => 'Demo Name'
        ];

        $conversation = new Conversation();
        $conversation->setName('demo')
            ->setDisplayName('Demo Name');

        return [
            [clone $conversation, 'create'],
            [clone $conversation, 'post'],
            [$raw, 'create'],
            [$raw, 'post'],
        ];
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
